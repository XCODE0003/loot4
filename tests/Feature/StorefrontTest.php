<?php

namespace Tests\Feature;

use App\Enums\FieldType;
use App\Enums\GameStatus;
use App\Enums\PricingMode;
use App\Enums\ProductStatus;
use App\Models\Game;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_with_discover_games_from_db(): void
    {
        Game::factory()->create(['status' => GameStatus::Active]);

        $this->get('/')->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('loot4/Home')
                ->has('discoverGames'),
        );
    }

    public function test_game_catalog_lists_active_products(): void
    {
        Product::factory()->create(['status' => ProductStatus::Active, 'visibility' => true]);
        Product::factory()->create(['status' => ProductStatus::Draft, 'visibility' => true]);

        $this->get('/game')->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('loot4/Game')
                ->has('products', 1) // only the active+visible one
                ->has('products.0', fn (Assert $p) => $p
                    ->where('priceNew', fn ($v) => is_numeric($v))
                    ->has('slug')
                    ->has('title')
                    ->has('image')
                    ->etc())
                ->has('gameFilters'),
        );
    }

    public function test_product_page_loads_product_by_slug(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'status' => ProductStatus::Active,
        ]);

        $this->get('/product/test-product')->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('loot4/Product')
                ->where('product.title', $product->name)
                ->where('product.slug', 'test-product')
                ->has('product.packages')
                ->has('product.platforms')
                ->has('product.optionGroups')
                ->has('product.breadcrumb'),
        );
    }

    public function test_variant_product_price_is_cheapest_option(): void
    {
        $product = Product::factory()->create([
            'slug' => 'variant-product',
            'status' => ProductStatus::Active,
            'price' => 5,
        ]);
        $form = $product->forms()->create(['name' => 'Config', 'is_active' => true, 'sort_order' => 0]);
        $form->fields()->create([
            'label' => 'Amount',
            'key' => 'amount',
            'type' => FieldType::Radio,
            'pricing_mode' => PricingMode::Absolute,
            'required' => true,
            'sort_order' => 0,
            'options' => [
                ['label' => '$10M', 'value' => '10m', 'extra_price' => 21.46],
                ['label' => '$25M', 'value' => '25m', 'extra_price' => 30.05],
            ],
        ]);

        $this->get('/product/variant-product')->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('loot4/Product')
                ->where('product.price', 21.46) // cheapest absolute option, not the base price
                ->has('product.optionGroups', 1)
                ->where('product.optionGroups.0.pricingMode', 'absolute')
                ->where('product.optionGroups.0.type', 'single')
                ->etc(),
        );
    }

    public function test_option_discount_inflates_struck_through_old_price_only(): void
    {
        $product = Product::factory()->create([
            'slug' => 'discount-product',
            'status' => ProductStatus::Active,
            'price' => 5,
        ]);
        $form = $product->forms()->create(['name' => 'Config', 'is_active' => true, 'sort_order' => 0]);
        $form->fields()->create([
            'label' => 'Amount',
            'key' => 'amount',
            'type' => FieldType::Radio,
            'pricing_mode' => PricingMode::Absolute,
            'required' => true,
            'sort_order' => 0,
            'options' => [
                ['label' => '$100M', 'value' => '100m', 'extra_price' => 100, 'discount' => 25],
            ],
        ]);

        $this->get('/product/discount-product')->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('loot4/Product')
                // Charged price stays the real 100 (the discount never reduces it).
                ->where('product.price', 100)
                ->where('product.optionGroups.0.options.0.price', 100)
                // Struck-through "old" price is inflated: 100 + 25% = 125.
                ->where('product.optionGroups.0.options.0.priceOld', 125)
                ->etc(),
        );
    }

    public function test_product_page_falls_back_to_first_product_without_slug(): void
    {
        Product::factory()->create(['status' => ProductStatus::Active]);

        $this->get('/product')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('loot4/Product')->has('product.title'),
        );
    }
}
