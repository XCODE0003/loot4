<?php

namespace App\Http\Controllers;

use App\Enums\FieldType;
use App\Enums\GameStatus;
use App\Enums\OptionsLayout;
use App\Enums\ProductStatus;
use App\Models\Game;
use App\Models\Product;
use App\Models\ProductFormField;
use App\Services\Products\ProductPricing;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function __construct(private readonly ProductPricing $pricing) {}

    /** @var list<string> */
    private const PAYMENT_ICONS = [
        '/payment_methods/cards.svg',
        '/payment_methods/amazon-pay-white.svg',
        '/payment_methods/apple-pay.svg',
        '/payment_methods/bancontact.svg',
        '/payment_methods/eps-white.svg',
        '/payment_methods/google-pay.svg',
        '/payment_methods/google-pay (1).svg',
        '/payment_methods/klarna.svg',
    ];

    public function home(): Response
    {
        $discover = Game::query()
            ->where('status', GameStatus::Active->value)
            ->orderBy('sort_order')
            ->get()
            ->values()
            ->map(fn (Game $game, int $i): array => [
                'slug' => $game->slug,
                'image' => $game->optimizedMediaUrl('discover_image')
                    ?: $game->optimizedMediaUrl('image'),
                'alt' => $game->name,
            ]);

        return Inertia::render('loot4/Home', [
            'discoverGames' => $discover->all() ?: null,
        ]);
    }

    public function game(): Response
    {
        $products = Product::query()
            ->where('status', ProductStatus::Active->value)
            ->where('visibility', true)
            ->with(['game', 'forms.fields'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $featured = Game::query()
            ->where('status', GameStatus::Active->value)
            ->orderBy('sort_order')
            ->first();

        return Inertia::render('loot4/Game', [
            'products' => $products->map(fn (Product $p, int $i): array => $this->card($p, $i))->all(),
            'gameFilters' => null,
            'showSearch' => true,
            // Generic catalog landing — not a specific game.
            'metaTitle' => 'Choose Game — Loot4you',
            'gamePage' => $featured ? [
                'title' => $featured->name,
                'image' => $featured->optimizedMediaUrl('image'),
                'guarantees' => $this->guarantees($featured),
            ] : null,
        ]);
    }

    public function gameBySlug(string $slug): Response
    {
        $game = Game::query()
            ->where('slug', $slug)
            ->where('status', GameStatus::Active->value)
            ->firstOrFail();

        $products = Product::query()
            ->where('status', ProductStatus::Active->value)
            ->where('visibility', true)
            ->where('game_id', $game->id)
            ->with(['game', 'forms.fields'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('loot4/Game', [
            'products' => $products->map(fn (Product $p, int $i): array => $this->card($p, $i))->all(),
            'gameFilters' => $this->gameFilters($game),
            'showSearch' => (bool) ($game->show_search ?? true),
            // Browser-tab / SEO title: the admin's custom meta_title wins, else
            // the game name with the brand suffix.
            'metaTitle' => $game->meta_title ?: $game->name.' — Loot4you',
            'gamePage' => [
                'title' => $game->name,
                'image' => $game->optimizedMediaUrl('image'),
                'guarantees' => $this->guarantees($game),
            ],
        ]);
    }

    public function product(?string $slug = null): Response
    {
        $product = Product::query()
            ->when($slug, fn ($q) => $q->where('slug', $slug))
            ->with(['game', 'forms.fields'])
            ->first()
            ?? Product::query()->with(['game', 'forms.fields'])->firstOrFail();

        return Inertia::render('loot4/Product', [
            'product' => $this->productDetails($product),
        ]);
    }

    /**
     * Map a product to the storefront catalog card shape.
     *
     * @return array<string, mixed>
     */
    private function card(Product $product, int $index = 0): array
    {
        // "From" price: cheapest selectable option for variant products, else the base price.
        $price = $this->pricing->fromPrice($product);
        $comparePrice = $product->compare_price !== null ? (float) $product->compare_price : null;

        return [
            'id' => (string) $product->id,
            'slug' => $product->slug,
            'title' => $product->name,
            'image' => $product->optimizedMediaUrl('main'),
            'category' => $product->type->value,
            'filterValues' => $product->filter_values ?? [],
            'priceOld' => ($comparePrice !== null && $comparePrice !== $price) ? $comparePrice : null,
            'priceNew' => $price,
        ];
    }

    /**
     * Map a product to the full product-page shape expected by ProductDetails.
     *
     * @return array<string, mixed>
     */
    private function productDetails(Product $product): array
    {
        $price = (float) $product->price;
        $comparePrice = $product->compare_price !== null ? (float) $product->compare_price : null;

        $recommended = Product::query()
            ->where('status', ProductStatus::Active->value)
            ->when($product->game_id, fn ($q) => $q->where('game_id', $product->game_id))
            ->whereKeyNot($product->id)
            ->with('forms.fields')
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->values()
            ->map(fn (Product $p, int $i): array => $this->card($p, $i))
            ->all();

        return [
            'slug' => $product->slug,
            'title' => $product->name,
            'image' => $product->optimizedMediaUrl('main'),
            'gallery' => $this->galleryImages($product),
            'trustImage' => 'product_trust.png',
            'breadcrumb' => [
                'game' => $product->game?->name ?? 'Games',
                'gameTo' => $product->game ? '/game/'.$product->game->slug : '/game',
                'gameIcon' => $product->game?->getFirstMediaUrl('icon') ?: $product->game?->optimizedMediaUrl('image'),
                'name' => $product->name,
            ],
            'payments' => self::PAYMENT_ICONS,
            'platforms' => $this->platforms($product),
            // Displayed/starting price: cheapest selectable option for variant products.
            'price' => $this->pricing->fromPrice($product),
            'priceOld' => ($comparePrice !== null && $comparePrice !== $price) ? $comparePrice : null,
            'packages' => $this->packages($product, $price),
            'optionGroups' => $this->optionGroups($product),
            'optionsLayout' => ($product->options_layout ?? OptionsLayout::Single)->value,
            'description' => (string) ($product->description ?? ''),
            // Prefer the dedicated HTML field; fall back to the plain description so
            // raw HTML pasted there still renders as-is (descriptions are admin-entered).
            'descriptionHtml' => (string) ($product->html_description ?: ($product->description ?? '')),
            'recommended' => $recommended,
        ];
    }

    /**
     * Game filter config for the storefront filter tab bar.
     *
     * @return array<string, mixed>|null
     */
    private function gameFilters(Game $game): ?array
    {
        $config = $game->game_filters;

        if (empty($config['label']) || empty($config['values'])) {
            return null;
        }

        return [
            'label' => (string) $config['label'],
            'values' => array_values(array_filter((array) $config['values'])),
        ];
    }

    /**
     * Guarantee texts for a game. Falls back to the two standard guarantees.
     *
     * @return list<string>
     */
    private function guarantees(Game $game): array
    {
        $saved = array_values(array_filter((array) ($game->guarantees ?? [])));

        if ($saved !== []) {
            return $saved;
        }

        return [
            'Money Back Guarantee — we stand firmly behind the quality of our service.',
            'Superior Support — our specialists are always available to help you with setup.',
        ];
    }

    /**
     * Platforms derived from a dynamic form field, with a sensible fallback.
     *
     * @return list<string>
     */
    private function platforms(Product $product): array
    {
        $field = $product->forms
            ->flatMap(fn ($form) => $form->fields)
            ->first(fn ($f): bool => str_contains(strtolower($f->key.' '.$f->label), 'platform') && filled($f->options));

        if ($field) {
            return collect($field->options)
                ->map(fn ($o): string => (string) ($o['label'] ?? $o['value'] ?? ''))
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * Packages derived from the first option-bearing form field, with a fallback.
     *
     * @return list<array<string, mixed>>
     */
    private function packages(Product $product, float $basePrice): array
    {
        $field = $product->forms
            ->flatMap(fn ($form) => $form->fields)
            ->first(fn ($f): bool => filled($f->options));

        $packages = collect($field?->options ?? [])
            ->values()
            ->map(fn ($o, int $i): array => [
                'id' => 'opt-'.$i,
                'parts' => [(string) ($o['label'] ?? $o['value'] ?? 'Option')],
                'price' => round($basePrice + (float) ($o['extra_price'] ?? 0), 2),
            ])
            ->all();

        return $packages;
    }

    /**
     * All product images for the gallery: the main image first, then the gallery
     * collection, de-duplicated.
     *
     * @return list<string>
     */
    private function galleryImages(Product $product): array
    {
        $url = fn ($m): string => $m->hasGeneratedConversion('web') ? $m->getUrl('web') : $m->getUrl();

        return collect([$product->optimizedMediaUrl('main')])
            ->merge($product->getMedia('gallery')->map($url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Selectable option groups (price selectors + paid add-ons) and free-form
     * input fields for the product page, built from the product's active
     * dynamic-form fields. A group with pricingMode 'absolute' is a "choose
     * amount" selector whose option price is the full price; 'addon' option
     * prices add to the total. Input fields (text/number/textarea) render as a
     * typed field whose `price` (base extra price) is added when filled.
     *
     * @return list<array<string, mixed>>
     */
    private function optionGroups(Product $product): array
    {
        // Fields across all active forms, in display order.
        $fields = $product->forms
            ->filter(fn ($form): bool => (bool) $form->is_active)
            ->sortBy('sort_order')
            ->flatMap(fn ($form) => $form->fields->sortBy('sort_order')->values())
            ->filter(fn ($field): bool => ($field->type->hasOptions() && filled($field->options)) || $field->type->isInput())
            ->values();

        // "block" = the wizard step a field belongs to (step-by-step layout).
        // A field with an explicit `step` number shares that step with any other
        // field of the same number; a field with no step gets its own step. So
        // the default is one-field-per-step, and the admin merges fields by
        // giving them the same Step number.
        return $fields->map(fn ($field): array => array_merge(
            $field->type->isInput() ? $this->inputGroup($field) : $this->choiceGroup($field),
            ['block' => $field->step !== null ? 's'.$field->step : 'f'.$field->id],
        ))->all();
    }

    /**
     * Shape for a choice field (select / radio / checkbox).
     *
     * @return array<string, mixed>
     */
    private function choiceGroup(ProductFormField $field): array
    {
        return [
            'key' => $field->key,
            'label' => $field->label,
            'type' => $field->type === FieldType::Checkbox ? 'multi' : 'single',
            'control' => $field->type->value, // 'select' (dropdown) | 'radio' | 'checkbox'
            'pricingMode' => $field->pricing_mode->value,
            'required' => (bool) $field->required,
            'tooltip' => (string) ($field->tooltip ?? ''),
            // Radio/checkbox layout: 1 or 2 options per row (selects ignore it).
            'columns' => max(1, min(2, (int) ($field->options_columns ?? 1))),
            'options' => collect($field->options)
                ->map(function ($o): array {
                    // Marketing "fake" discount: the customer is charged the real
                    // `price` (= extra_price); the admin types the struck-through
                    // "old" price directly in `discount`. It's only shown when it's
                    // actually higher than the charged price. The price is never reduced.
                    $price = round((float) ($o['extra_price'] ?? 0), 2);
                    $priceOld = round((float) ($o['discount'] ?? 0), 2);

                    return [
                        'value' => (string) ($o['value'] ?? $o['label'] ?? ''),
                        'label' => (string) ($o['label'] ?? $o['value'] ?? ''),
                        'price' => $price,
                        'priceOld' => $priceOld > $price ? $priceOld : null,
                        'tooltip' => (string) ($o['tooltip'] ?? ''),
                        'popular' => (bool) ($o['popular'] ?? false),
                        'default' => (bool) ($o['default'] ?? false),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * Shape for a free-form input field (text / number / textarea). It has no
     * options; the customer types a value that is stored on the order and whose
     * `price` (base extra price) is added to the total when filled.
     *
     * @return array<string, mixed>
     */
    private function inputGroup(ProductFormField $field): array
    {
        return [
            'key' => $field->key,
            'label' => $field->label,
            'type' => 'single',
            'control' => $field->type->value, // 'text' | 'number' | 'textarea'
            'pricingMode' => 'addon',
            'required' => (bool) $field->required,
            'tooltip' => (string) ($field->tooltip ?? ''),
            'price' => round((float) ($field->extra_price ?? 0), 2),
            'maxLength' => ProductPricing::INPUT_MAX_LENGTH,
            'columns' => 1,
            'options' => [],
        ];
    }
}
