<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Enums\ProductStatus;
use App\Models\Game;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /** @var list<string> */

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
                'image' => $game->getFirstMediaUrl('discover_image')
                    ?: $game->getFirstMediaUrl('image')
                    ?: null,
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
            ->with('game')
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
            'gamePage' => $featured ? [
                'title' => $featured->name,
                'image' => $featured->getFirstMediaUrl('image') ?: null,
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
            ->with('game')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('loot4/Game', [
            'products' => $products->map(fn (Product $p, int $i): array => $this->card($p, $i))->all(),
            'gameFilters' => $this->gameFilters($game),
            'gamePage' => [
                'title' => $game->name,
                'image' => $game->getFirstMediaUrl('image') ?: null,
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
        $price = (float) $product->price;
        $comparePrice = $product->compare_price !== null ? (float) $product->compare_price : null;

        return [
            'id' => (string) $product->id,
            'slug' => $product->slug,
            'title' => $product->name,
            'image' => $product->getFirstMediaUrl('main') ?: null,
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
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->values()
            ->map(fn (Product $p, int $i): array => $this->card($p, $i))
            ->all();

        return [
            'slug' => $product->slug,
            'title' => $product->name,
            'image' => $product->getFirstMediaUrl('main') ?: null,
            'trustImage' => 'product_trust.png',
            'breadcrumb' => [
                'game' => $product->game?->name ?? 'Games',
                'gameTo' => $product->game ? '/game/'.$product->game->slug : '/game',
                'gameIcon' => $product->game?->getFirstMediaUrl('icon') ?: $product->game?->getFirstMediaUrl('image') ?: null,
                'name' => $product->name,
            ],
            'payments' => self::PAYMENT_ICONS,
            'platforms' => $this->platforms($product),
            'price' => $price,
            'priceOld' => ($comparePrice !== null && $comparePrice !== $price) ? $comparePrice : null,
            'packages' => $this->packages($product, $price),
            'description' => (string) ($product->description ?? ''),
            'descriptionHtml' => (string) ($product->html_description ?? ''),
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
}
