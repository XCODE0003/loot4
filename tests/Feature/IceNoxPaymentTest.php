<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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
        $this->app->forgetInstance(\App\Services\Payments\IceNoxGateway::class);
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
            'items' => [['slug' => 'gta-cash', 'qty' => 1]],
            'method' => 'stripe-cards',
        ]);

        $response->assertRedirect('https://imp.icenox.com/v1/payment/get/pay_abc123');

        $order = Order::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame('pay_abc123', $order->payments()->first()->transaction_id);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/payment/create/')
            && $request['paymentmethod'] === 'stripe-cards'
            && $request->hasHeader('Authorization', 'Bearer test-key'));
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
        $this->app->forgetInstance(\App\Services\Payments\IceNoxGateway::class);

        Product::factory()->create(['slug' => 'p2', 'status' => ProductStatus::Active, 'price' => 10]);

        $this->post('/checkout', [
            'email' => 'z@z.com',
            'items' => [['slug' => 'p2', 'qty' => 1]],
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['email' => 'z@z.com']);
    }
}
