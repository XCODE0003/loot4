<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Enums\FieldType;
use App\Enums\PricingMode;
use App\Enums\ProductStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_validation_endpoint(): void
    {
        Coupon::factory()->create([
            'code' => 'SAVE10',
            'type' => CouponType::Percentage,
            'value' => 10,
            'is_active' => true,
            'expires_at' => null,
            'usage_limit' => null,
            'min_order_amount' => null,
        ]);

        $this->getJson('/cart/coupon?code=SAVE10&subtotal=100')
            ->assertOk()
            ->assertJson(['valid' => true, 'type' => 'percentage', 'value' => 10]);

        $this->getJson('/cart/coupon?code=NOPE&subtotal=100')
            ->assertOk()
            ->assertJson(['valid' => false]);
    }

    public function test_checkout_creates_pending_order_with_recomputed_prices(): void
    {
        Product::factory()->create(['slug' => 'gta-cash', 'status' => ProductStatus::Active, 'price' => 20]);

        $this->post('/checkout', [
            'email' => 'buyer@example.com',
            'items' => [['slug' => 'gta-cash', 'qty' => 2, 'option' => 'PS4 · $10M']],
            'coupon' => null,
        ])->assertRedirect();

        $order = Order::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertEquals(40, (float) $order->total);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame('PS4 · $10M', $order->items()->first()->form_data['option']);
    }

    public function test_checkout_recomputes_price_from_variant_and_addon_selections(): void
    {
        $product = Product::factory()->create([
            'slug' => 'gta-cash',
            'status' => ProductStatus::Active,
            'price' => 5,
        ]);
        $form = $product->forms()->create(['name' => 'Config', 'is_active' => true, 'sort_order' => 0]);
        $form->fields()->create([
            'label' => 'Amount', 'key' => 'amount', 'type' => FieldType::Radio,
            'pricing_mode' => PricingMode::Absolute, 'required' => true, 'sort_order' => 0,
            'options' => [
                ['label' => '$10M', 'value' => '10m', 'extra_price' => 21.46],
                ['label' => '$25M', 'value' => '25m', 'extra_price' => 30.05],
            ],
        ]);
        $form->fields()->create([
            'label' => 'Add-ons', 'key' => 'addons', 'type' => FieldType::Checkbox,
            'pricing_mode' => PricingMode::Addon, 'required' => false, 'sort_order' => 1,
            'options' => [['label' => 'Max Stats', 'value' => 'max', 'extra_price' => 10]],
        ]);

        $this->post('/checkout', [
            'email' => 'buyer@example.com',
            'items' => [[
                'slug' => 'gta-cash',
                'qty' => 1,
                // Client-sent price is irrelevant — the server recomputes from selections.
                'selections' => ['amount' => '25m', 'addons' => ['max']],
                'option' => 'tampered label',
            ]],
            'coupon' => null,
        ])->assertRedirect();

        $order = Order::where('email', 'buyer@example.com')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertEquals(40.05, (float) $order->total); // 30.05 + 10
        $this->assertEquals(40.05, (float) $item->price);
        $this->assertSame('$25M · Max Stats', $item->form_data['option']); // server-built summary
        // Per-field breakdown stored under field labels for line-by-line display.
        $this->assertSame('$25M', $item->form_data['Amount']);
        $this->assertSame('Max Stats', $item->form_data['Add-ons']);
        $this->assertArrayNotHasKey('selections', $item->form_data);
    }

    public function test_checkout_drops_line_missing_a_required_selection(): void
    {
        $product = Product::factory()->create([
            'slug' => 'gta-cash',
            'status' => ProductStatus::Active,
            'price' => 5,
        ]);
        $form = $product->forms()->create(['name' => 'Config', 'is_active' => true, 'sort_order' => 0]);
        $form->fields()->create([
            'label' => 'Amount', 'key' => 'amount', 'type' => FieldType::Radio,
            'pricing_mode' => PricingMode::Absolute, 'required' => true, 'sort_order' => 0,
            'options' => [['label' => '$10M', 'value' => '10m', 'extra_price' => 21.46]],
        ]);

        // Bogus selection for a required group → line dropped → no valid items.
        $this->post('/checkout', [
            'email' => 'buyer@example.com',
            'items' => [['slug' => 'gta-cash', 'qty' => 1, 'selections' => ['amount' => 'bogus']]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Order::where('email', 'buyer@example.com')->count());
    }

    public function test_checkout_applies_coupon_discount(): void
    {
        Product::factory()->create(['slug' => 'p1', 'status' => ProductStatus::Active, 'price' => 100]);
        Coupon::factory()->create([
            'code' => 'TENOFF',
            'type' => CouponType::Fixed,
            'value' => 10,
            'is_active' => true,
            'expires_at' => null,
            'usage_limit' => null,
            'min_order_amount' => null,
        ]);

        $this->post('/checkout', [
            'email' => 'a@b.com',
            'items' => [['slug' => 'p1', 'qty' => 1]],
            'coupon' => 'TENOFF',
        ])->assertRedirect();

        $order = Order::where('email', 'a@b.com')->firstOrFail();
        $this->assertEquals(90, (float) $order->total);
        $this->assertEquals(10, (float) $order->discount);
    }

    public function test_checkout_rejects_invalid_email(): void
    {
        Product::factory()->create(['slug' => 'p9', 'status' => ProductStatus::Active]);

        $this->post('/checkout', [
            'email' => 'not-an-email',
            'items' => [['slug' => 'p9', 'qty' => 1]],
        ])->assertSessionHasErrors('email');
    }

    public function test_success_page_renders(): void
    {
        Product::factory()->create(['slug' => 'p2', 'status' => ProductStatus::Active, 'price' => 15]);
        $this->post('/checkout', ['email' => 'c@d.com', 'items' => [['slug' => 'p2', 'qty' => 1]]]);
        $order = Order::where('email', 'c@d.com')->firstOrFail();

        $this->get('/checkout/success/'.$order->order_number)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('loot4/CheckoutSuccess')
                ->where('order.number', $order->order_number)
                ->has('order.items', 1));
    }
}
