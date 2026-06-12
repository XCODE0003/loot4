<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Mail\NewOrderMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Notifications\OrderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create(['email' => 'buyer@example.com', 'total' => 96.98, 'currency' => 'USD']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'GTA 5 Online - Cash + Cars PS4&PS5',
            'quantity' => 1,
            'price' => 96.98,
            'status' => DeliveryStatus::Pending,
            'form_data' => ['Select platform' => 'PlayStation 5', 'Package money' => '500 Million GTA Cash & Cars'],
        ]);
        $order->payments()->create([
            'method' => 'stripe-klarna',
            'amount' => 96.98,
            'currency' => 'USD',
            'status' => PaymentStatus::Paid,
        ]);

        return $order;
    }

    public function test_paid_order_sends_telegram_and_emails(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        Setting::set('telegram_bot_token', 'ORDERS_TOKEN');
        Setting::set('telegram_chat_id', '12345');
        Setting::set('order_notify_email', 'staff@loot4you.gg');

        $order = $this->makeOrder();
        app(OrderNotifier::class)->paid($order);

        Http::assertSent(fn ($r) => str_contains($r->url(), '/botORDERS_TOKEN/sendMessage')
            && str_contains($r['text'], 'NEW ORDER RECEIVED')
            && str_contains($r['text'], $order->order_number)
            && str_contains($r['text'], 'ICENOX-KLARNA')
            && str_contains($r['text'], 'Select platform: PlayStation 5'));

        Mail::assertSent(OrderConfirmationMail::class, fn ($m) => $m->hasTo('buyer@example.com'));
        Mail::assertSent(NewOrderMail::class, fn ($m) => $m->hasTo('staff@loot4you.gg'));
    }

    public function test_failed_order_uses_failed_bot(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        Setting::set('telegram_failed_bot_token', 'FAILED_TOKEN');
        Setting::set('telegram_failed_chat_id', '999');

        $order = $this->makeOrder();
        app(OrderNotifier::class)->failed($order, 'card declined');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/botFAILED_TOKEN/sendMessage')
            && str_contains($r['text'], 'ORDER FAILED')
            && str_contains($r['text'], 'card declined'));
    }

    public function test_no_telegram_call_without_credentials(): void
    {
        Http::fake();
        Mail::fake();

        app(OrderNotifier::class)->paid($this->makeOrder());

        Http::assertNothingSent();
    }

    public function test_emails_render_without_errors(): void
    {
        $order = $this->makeOrder();

        $this->assertStringContainsString('Thank you for your order', (new OrderConfirmationMail($order))->render());
        $this->assertStringContainsString('New order received', (new NewOrderMail($order))->render());
    }
}
