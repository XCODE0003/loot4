<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Payments\IceNoxGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IceNoxPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function configureGateway(): void
    {
        config([
            'services.icenox.key' => 'test-key',
            'services.icenox.merchant' => '20233',
            'services.icenox.url' => 'https://imp.icenox.com',
        ]);
        $this->app->forgetInstance(IceNoxGateway::class);
    }

    public function test_checkout_redirects_to_icenox_payment_url(): void
    {
        $this->configureGateway();
        Http::fake([
            'imp.icenox.com/*' => Http::response([
                'success' => true,
                'paymentid' => 'pay_abc123',
                'orderid' => '301-20233-ORD',
                'url' => 'https://imp.icenox.com/v1/payment/get/pay_abc123',
            ], 200),
        ]);

        Product::factory()->create(['slug' => 'gta-cash', 'status' => ProductStatus::Active, 'price' => 25]);

        $response = $this->post('/checkout', [
            'email' => 'buyer@example.com',
            'first_name' => 'John', 'last_name' => 'Doe', 'country' => 'US',
            'town' => 'LA', 'address' => '1 St', 'postal_code' => '90001',
            'items' => [['slug' => 'gta-cash', 'qty' => 1]],
            'method' => 'stripe-cards',
        ]);

        $response->assertRedirect('https://imp.icenox.com/v1/payment/get/pay_abc123');

        $order = Order::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame('pay_abc123', $order->payments()->first()->transaction_id);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/payment/create/')
            && $request['paymentmethod'] === 'stripe-cards'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            // Customer info IceNox needs to create the Stripe Customer (name + email
            // attached to the transaction). A guest has no account id, so customer_id
            // falls back to the email — it must never be empty, or no Customer is made.
            && $request['customer_name'] === 'John Doe'
            && $request['customer_email'] === 'buyer@example.com'
            && $request['customer_id'] === 'buyer@example.com');
    }

    public function test_paid_delivery_fee_is_included_in_icenox_amount(): void
    {
        $this->configureGateway();
        Http::fake([
            'imp.icenox.com/*' => Http::response([
                'success' => true, 'paymentid' => 'pay_del', 'orderid' => 'ORD',
                'url' => 'https://imp.icenox.com/v1/payment/get/pay_del',
            ], 200),
        ]);

        // The exact production failure: item 29.99 + Express delivery 9.99 = 39.98.
        // IceNox validates total == amount - discount; if `amount` omits the fee it
        // rejects the order ("The parameter [total] is invalid") and payment never
        // starts, so `amount` must carry the delivery fee.
        $order = Order::factory()->create([
            'subtotal' => 29.99,
            'discount' => 0,
            'delivery_fee' => 9.99,
            'total' => 39.98,
            'currency' => 'USD',
        ]);

        app(IceNoxGateway::class)->createPayment($order, 'stripe-cards');

        Http::assertSent(function ($request): bool {
            $amount = (float) $request['amount'];
            $discount = (float) $request['discount'];
            $total = (float) $request['total'];

            return str_contains($request->url(), '/api/payment/create/')
                && abs($amount - 39.98) < 0.001
                && abs($total - 39.98) < 0.001
                // The invariant IceNox enforces — must hold exactly.
                && abs(($amount - $discount) - $total) < 0.001;
        });
    }

    public function test_checkout_shows_error_when_gateway_fails(): void
    {
        $this->configureGateway();
        Http::fake([
            'imp.icenox.com/*' => Http::response(['success' => false, 'error' => 400, 'message' => 'Bad method'], 200),
        ]);

        Product::factory()->create(['slug' => 'p1', 'status' => ProductStatus::Active, 'price' => 10]);

        $this->post('/checkout', [
            'email' => 'x@y.com',
            'first_name' => 'John', 'last_name' => 'Doe', 'country' => 'US',
            'town' => 'LA', 'address' => '1 St', 'postal_code' => '90001',
            'items' => [['slug' => 'p1', 'qty' => 1]],
            'method' => 'stripe-cards',
        ])->assertSessionHasErrors('payment');
    }

    public function test_webhook_marks_order_paid(): void
    {
        $order = Order::factory()->create(['payment_status' => PaymentStatus::Pending]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'transaction_id' => 'pay_xyz',
            'status' => PaymentStatus::Pending,
        ]);

        $this->postJson('/checkout/webhook', [
            'paymentid' => 'pay_xyz',
            'status' => 'paid',
        ])->assertOk()->assertJson(['received' => true]);

        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
    }

    public function test_checkout_without_gateway_falls_back_to_success(): void
    {
        config(['services.icenox.key' => null]);
        $this->app->forgetInstance(IceNoxGateway::class);

        Product::factory()->create(['slug' => 'p2', 'status' => ProductStatus::Active, 'price' => 10]);

        $this->post('/checkout', [
            'email' => 'z@z.com',
            'first_name' => 'John', 'last_name' => 'Doe', 'country' => 'US',
            'town' => 'LA', 'address' => '1 St', 'postal_code' => '90001',
            'items' => [['slug' => 'p2', 'qty' => 1]],
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['email' => 'z@z.com']);
    }
}
