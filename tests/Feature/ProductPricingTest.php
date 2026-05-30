<?php

namespace Tests\Feature;

use App\Enums\FieldType;
use App\Enums\PricingMode;
use App\Models\Product;
use App\Services\Products\ProductPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPricingTest extends TestCase
{
    use RefreshDatabase;

    private function pricing(): ProductPricing
    {
        return app(ProductPricing::class);
    }

    /**
     * Build a product with one price-selector (absolute) "amount" group and an
     * optional multi-select add-ons group.
     */
    private function variantProduct(): Product
    {
        $product = Product::factory()->create(['slug' => 'gta-cash', 'price' => 5]);

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
                ['label' => '$50M', 'value' => '50m', 'extra_price' => 38.64],
            ],
        ]);

        $form->fields()->create([
            'label' => 'Add-ons',
            'key' => 'addons',
            'type' => FieldType::Checkbox,
            'pricing_mode' => PricingMode::Addon,
            'required' => false,
            'sort_order' => 1,
            'options' => [
                ['label' => 'Max Stats', 'value' => 'max', 'extra_price' => 25],
                ['label' => 'LSC Unlocks', 'value' => 'lsc', 'extra_price' => 10],
            ],
        ]);

        return $product->fresh(['forms.fields']);
    }

    public function test_from_price_is_cheapest_absolute_option(): void
    {
        $product = $this->variantProduct();

        $this->assertSame(21.46, $this->pricing()->fromPrice($product));
    }

    public function test_from_price_falls_back_to_base_price_without_absolute_group(): void
    {
        $product = Product::factory()->create(['price' => 12.5]);

        $this->assertSame(12.5, $this->pricing()->fromPrice($product));
    }

    public function test_line_price_uses_absolute_option_plus_addons(): void
    {
        $product = $this->variantProduct();

        $price = $this->pricing()->linePrice($product, ['amount' => '25m', 'addons' => ['max', 'lsc']]);

        // 30.05 (selected absolute) + 25 + 10 (add-ons) — base price (5) is ignored.
        $this->assertSame(65.05, $price);
    }

    public function test_line_price_ignores_unknown_selection_values(): void
    {
        $product = $this->variantProduct();

        // Bogus add-on value is ignored; only the valid absolute option counts.
        $price = $this->pricing()->linePrice($product, ['amount' => '10m', 'addons' => ['hacker']]);

        $this->assertSame(21.46, $price);
    }

    public function test_summary_lists_selected_labels(): void
    {
        $product = $this->variantProduct();

        $summary = $this->pricing()->summary($product, ['amount' => '25m', 'addons' => ['max']]);

        $this->assertSame('$25M · Max Stats', $summary);
    }

    public function test_missing_required_selection_is_detected(): void
    {
        $product = $this->variantProduct();

        $this->assertTrue($this->pricing()->missingRequiredSelection($product, []));
        $this->assertFalse($this->pricing()->missingRequiredSelection($product, ['amount' => '10m']));
    }
}
