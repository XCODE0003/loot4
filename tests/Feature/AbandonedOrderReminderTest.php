<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Mail\AbandonedCartMail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AbandonedOrderReminderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function pendingOrder(array $attrs = []): Order
    {
        return Order::factory()->create([
            'email' => 'buyer@example.com',
            'payment_status' => PaymentStatus::Pending,
            'abandoned_reminded_at' => null,
            'created_at' => now()->subHours(2),
            ...$attrs,
        ]);
    }

    public function test_reminder_is_sent_for_an_unpaid_order_past_the_grace_period(): void
    {
        Mail::fake();
        $order = $this->pendingOrder();

        $this->artisan('orders:remind-abandoned')->assertSuccessful();

        Mail::assertSent(AbandonedCartMail::class, fn (AbandonedCartMail $m): bool => $m->hasTo('buyer@example.com') && $m->order->is($order));
        $this->assertNotNull($order->refresh()->abandoned_reminded_at);
    }

    public function test_recent_unpaid_order_is_not_reminded(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(['created_at' => now()->subMinutes(20)]);

        $this->artisan('orders:remind-abandoned')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($order->refresh()->abandoned_reminded_at);
    }

    public function test_paid_order_is_not_reminded(): void
    {
        Mail::fake();
        $this->pendingOrder(['payment_status' => PaymentStatus::Paid]);

        $this->artisan('orders:remind-abandoned')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_order_is_reminded_only_once(): void
    {
        Mail::fake();
        $this->pendingOrder();

        $this->artisan('orders:remind-abandoned')->assertSuccessful();
        $this->artisan('orders:remind-abandoned')->assertSuccessful();

        Mail::assertSent(AbandonedCartMail::class, 1);
    }

    public function test_very_old_orders_are_skipped(): void
    {
        Mail::fake();
        $this->pendingOrder(['created_at' => now()->subDays(30)]);

        $this->artisan('orders:remind-abandoned')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_pay_link_redirects_a_paid_order_to_the_success_page(): void
    {
        $order = Order::factory()->create(['payment_status' => PaymentStatus::Paid]);

        $url = URL::temporarySignedRoute('checkout.pay', now()->addDay(), ['order' => $order->order_number]);

        $this->get($url)->assertRedirect(route('checkout.success', $order->order_number));
    }

    public function test_pay_link_rejects_an_invalid_signature(): void
    {
        $order = Order::factory()->create();

        $this->get('/checkout/pay/'.$order->order_number)->assertForbidden();
    }
}
