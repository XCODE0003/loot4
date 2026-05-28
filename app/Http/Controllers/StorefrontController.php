<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Enums\ProductStatus;
use App\Models\Game;
use App\Models\Product;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /** @var list<string> */

    /** @var list<string> */
    private const PAYMENT_ICONS = [
        'product_visa.png', 'product_master.png', 'product_paypal.png',
        'product_pay.png', 'product_btc.png', 'product_china.png',
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
            ->latest()
            ->get();

        $filters = $products
            ->map(fn (Product $p): string => $p->type->value)
            ->unique()
            ->map(fn (string $type): array => ['label' => ucfirst($type), 'value' => $type])
            ->prepend(['label' => 'All', 'value' => 'all'])
            ->values()
            ->all();

        $featured = Game::query()
            ->where('status', GameStatus::Active->value)
            ->orderBy('sort_order')
            ->first();

        return Inertia::render('loot4/Game', [
            'products' => $products->map(fn (Product $p, int $i): array => $this->card($p, $i))->all(),
            'filters' => $filters,
            'gamePage' => $featured ? [
                'title' => $featured->name,
                'image' => $featured->getFirstMediaUrl('image') ?: null,
                'guarantees' => [
                    'Money Back Guarantee — we stand firmly behind the quality of our service.',
                    'Superior Support — our specialists are always available to help you with setup.',
                ],
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
            ->latest()
            ->get();

        $filters = $products
            ->map(fn (Product $p): string => $p->type->value)
            ->unique()
            ->map(fn (string $type): array => ['label' => ucfirst($type), 'value' => $type])
            ->prepend(['label' => 'All', 'value' => 'all'])
            ->values()
            ->all();

        return Inertia::render('loot4/Game', [
            'products' => $products->map(fn (Product $p, int $i): array => $this->card($p, $i))->all(),
            'filters' => $filters,
            'gamePage' => [
                'title' => $game->name,
                'image' => $game->getFirstMediaUrl('image') ?: null,
                'guarantees' => [
                    'Money Back Guarantee — we stand firmly behind the quality of our service.',
                    'Superior Support — our specialists are always available to help you with setup.',
                ],
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
        return [
            'id' => (string) $product->id,
            'slug' => $product->slug,
            'title' => $product->name,
            'image' => $product->getFirstMediaUrl('main') ?: null,
            'category' => $product->type->value,
            'priceOld' => (float) ($product->compare_price ?? $product->price),
            'priceNew' => (float) $product->price,
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
        $recommended = Product::query()
            ->where('status', ProductStatus::Active->value)
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
                'gameTo' => '/game',
                'name' => $product->name,
                'platformIcon' => 'product_xbox.png',
            ],
            'payments' => self::PAYMENT_ICONS,
            'platforms' => $this->platforms($product),
            'price' => $price,
            'priceOld' => (float) ($product->compare_price ?? $price),
            'packages' => $this->packages($product, $price),
            'description' => (string) ($product->description ?? ''),
            'recommended' => $recommended,
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

        return ['PS4', 'PS5', 'PC'];
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

        if ($packages === []) {
            return [[
                'id' => 'base',
                'parts' => [$product->name],
                'price' => $basePrice,
            ]];
        }

        return $packages;
    }
}
