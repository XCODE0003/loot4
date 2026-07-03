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

    /**
     * Required billing fields the checkout now expects.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function billing(array $overrides = []): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'country' => 'US',
            'state' => 'California',
            'town' => 'Los Angeles',
            'address' => '1 Market St',
            'postal_code' => '90001',
            ...$overrides,
        ];
    }

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
            ...$this->billing(),
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
            ...$this->billing(),
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
            ...$this->billing(),
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
            ...$this->billing(),
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
        $this->post('/checkout', [...$this->billing(), 'email' => 'c@d.com', 'items' => [['slug' => 'p2', 'qty' => 1]]]);
        $order = Order::where('email', 'c@d.com')->firstOrFail();

        $this->get('/checkout/success/'.$order->order_number)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('loot4/CheckoutSuccess')
                ->where('order.number', $order->order_number)
                ->has('order.items', 1));
    }

    public function test_checkout_requires_billing_fields(): void
    {
        Product::factory()->create(['slug' => 'p3', 'status' => ProductStatus::Active, 'price' => 10]);

        $this->post('/checkout', [
            'email' => 'buyer@example.com',
            'items' => [['slug' => 'p3', 'qty' => 1]],
        ])->assertSessionHasErrors(['first_name', 'last_name', 'country', 'town', 'address', 'postal_code']);

        $this->assertSame(0, Order::where('email', 'buyer@example.com')->count());
    }

    public function test_billing_details_are_stored_on_the_order(): void
    {
        Product::factory()->create(['slug' => 'p4', 'status' => ProductStatus::Active, 'price' => 25]);

        $this->post('/checkout', [
            ...$this->billing(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'phone' => '+15550001', 'postal_code' => '10001']),
            'email' => 'ada@example.com',
            'items' => [['slug' => 'p4', 'qty' => 1]],
        ])->assertRedirect();

        $order = Order::where('email', 'ada@example.com')->firstOrFail();
        $this->assertSame('Ada', $order->first_name);
        $this->assertSame('Lovelace', $order->last_name);
        $this->assertSame('+15550001', $order->phone);
        $this->assertSame('US', $order->country);
        $this->assertSame('California', $order->state);
        $this->assertSame('Los Angeles', $order->town);
        $this->assertSame('10001', $order->postal_code);
    }

    public function test_chosen_delivery_option_adds_its_price(): void
    {
        Product::factory()->create([
            'slug' => 'dp1', 'status' => ProductStatus::Active, 'price' => 30,
            'delivery_options' => [
                ['label' => 'Standard (1–24h)', 'price' => 0],
                ['label' => 'Express (1–12h)', 'price' => 9.99],
            ],
        ]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'dopt@example.com',
            'items' => [['slug' => 'dp1', 'qty' => 1]],
            'delivery' => 'Express (1–12h)',
        ])->assertRedirect();

        $order = Order::where('email', 'dopt@example.com')->firstOrFail();
        $this->assertSame('Express (1–12h)', $order->delivery_method);
        $this->assertEquals(9.99, (float) $order->delivery_fee);
        $this->assertEquals(39.99, (float) $order->total); // 30 + 9.99
    }

    public function test_free_delivery_option_costs_nothing(): void
    {
        Product::factory()->create([
            'slug' => 'dp2', 'status' => ProductStatus::Active, 'price' => 30,
            'delivery_options' => [['label' => 'Standard', 'price' => 0], ['label' => 'Express', 'price' => 5]],
        ]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'freeopt@example.com',
            'items' => [['slug' => 'dp2', 'qty' => 1]],
            'delivery' => 'Standard',
        ])->assertRedirect();

        $order = Order::where('email', 'freeopt@example.com')->firstOrFail();
        $this->assertSame('Standard', $order->delivery_method);
        $this->assertEquals(0, (float) $order->delivery_fee);
        $this->assertEquals(30, (float) $order->total);
    }

    public function test_delivery_price_cannot_be_tampered_with(): void
    {
        // The product's Express is $5, but the client claims a different amount by
        // sending an unknown label — the server ignores it and charges $0.
        Product::factory()->create([
            'slug' => 'dp3', 'status' => ProductStatus::Active, 'price' => 30,
            'delivery_options' => [['label' => 'Express', 'price' => 5]],
        ]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'tamper@example.com',
            'items' => [['slug' => 'dp3', 'qty' => 1]],
            'delivery' => 'Free VIP shipping', // not a real option for this product
        ])->assertRedirect();

        $order = Order::where('email', 'tamper@example.com')->firstOrFail();
        $this->assertEquals(0, (float) $order->delivery_fee);
        $this->assertEquals(30, (float) $order->total);
    }

    public function test_only_options_shared_by_all_items_are_chargeable(): void
    {
        // Both share "Express"; only d-a offers "VIP" → VIP isn't a valid order-wide choice.
        Product::factory()->create(['slug' => 'd-a', 'status' => ProductStatus::Active, 'price' => 20,
            'delivery_options' => [['label' => 'Express', 'price' => 5], ['label' => 'VIP', 'price' => 50]]]);
        Product::factory()->create(['slug' => 'd-b', 'status' => ProductStatus::Active, 'price' => 10,
            'delivery_options' => [['label' => 'Express', 'price' => 8]]]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'shared@example.com',
            'items' => [['slug' => 'd-a', 'qty' => 1], ['slug' => 'd-b', 'qty' => 1]],
            'delivery' => 'VIP',
        ])->assertRedirect();

        $order = Order::where('email', 'shared@example.com')->firstOrFail();
        $this->assertEquals(0, (float) $order->delivery_fee); // VIP not shared → free
        $this->assertEquals(30, (float) $order->total);
    }

    public function test_shared_delivery_option_uses_the_highest_price(): void
    {
        Product::factory()->create(['slug' => 'm-a', 'status' => ProductStatus::Active, 'price' => 20,
            'delivery_options' => [['label' => 'Express', 'price' => 5]]]);
        Product::factory()->create(['slug' => 'm-b', 'status' => ProductStatus::Active, 'price' => 10,
            'delivery_options' => [['label' => 'Express', 'price' => 8]]]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'maxprice@example.com',
            'items' => [['slug' => 'm-a', 'qty' => 1], ['slug' => 'm-b', 'qty' => 1]],
            'delivery' => 'Express',
        ])->assertRedirect();

        $order = Order::where('email', 'maxprice@example.com')->firstOrFail();
        $this->assertSame('Express', $order->delivery_method);
        $this->assertEquals(8, (float) $order->delivery_fee); // max(5, 8)
        $this->assertEquals(38, (float) $order->total); // 20 + 10 + 8
    }

    public function test_delivery_price_accepts_a_comma_decimal(): void
    {
        // Admin may save "19,99" (comma separator) — it must charge 19.99, not 19.
        Product::factory()->create([
            'slug' => 'dp-comma', 'status' => ProductStatus::Active, 'price' => 30,
            'delivery_options' => [['label' => 'Express', 'price' => '19,99']],
        ]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'comma@example.com',
            'items' => [['slug' => 'dp-comma', 'qty' => 1]],
            'delivery' => 'Express',
        ])->assertRedirect();

        $order = Order::where('email', 'comma@example.com')->firstOrFail();
        $this->assertEquals(19.99, (float) $order->delivery_fee);
        $this->assertEquals(49.99, (float) $order->total);
    }

    public function test_product_without_delivery_options_is_free(): void
    {
        Product::factory()->create(['slug' => 'p6', 'status' => ProductStatus::Active, 'price' => 30]);

        $this->post('/checkout', [
            ...$this->billing(),
            'email' => 'noopt@example.com',
            'items' => [['slug' => 'p6', 'qty' => 1]],
        ])->assertRedirect();

        $order = Order::where('email', 'noopt@example.com')->firstOrFail();
        $this->assertEquals(0, (float) $order->delivery_fee);
        $this->assertEquals(30, (float) $order->total);
    }
}
