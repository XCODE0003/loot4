<?php

namespace Tests\Feature;

use App\Enums\ConversionPlatform;
use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientConversionLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An order with neutral attribution (no paid source) so the eligibility
     * gate allows every platform — keeps these endpoint tests focused on the
     * logging/validation logic rather than source gating.
     *
     * @param  array<string, mixed>  $attrs
     */
    private function neutralOrder(array $attrs = []): Order
    {
        return Order::factory()->create([
            'source' => 'direct',
            'medium' => 'none',
            'gclid' => null,
            'fbclid' => null,
            'ttclid' => null,
            ...$attrs,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function payload(Order $order, array $overrides = []): array
    {
        return [
            'order' => $order->order_number,
            'platform' => 'google_ads',
            'event' => 'Purchase',
            'sent' => true,
            'reason' => 'sent',
            'url' => 'https://loot4you.gg/checkout/success/'.$order->order_number,
            ...$overrides,
        ];
    }

    public function test_sent_conversion_is_logged_with_order_value(): void
    {
        $order = $this->neutralOrder(['total' => 49.99, 'currency' => 'USD']);

        $this->postJson('/checkout/conversion', $this->payload($order))
            ->assertCreated()
            ->assertJson(['logged' => true]);

        $log = ConversionLog::firstOrFail();
        $this->assertSame(ConversionPlatform::GoogleAds, $log->platform);
        $this->assertSame('Purchase', $log->event);
        $this->assertSame(ConversionStatus::Sent, $log->status);
        $this->assertSame('sent', $log->reason);
        $this->assertSame($order->id, $log->order_id);
        $this->assertEquals(49.99, (float) $log->value);
        $this->assertSame('USD', $log->currency);
        $this->assertNotNull($log->sent_at);
        $this->assertSame('client', $log->request_payload['origin'] ?? null);
    }

    public function test_localstorage_skip_is_logged_as_skipped(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, [
            'sent' => false,
            'reason' => 'localStorage-skip',
        ]))->assertCreated();

        $this->assertSame(ConversionStatus::Skipped, ConversionLog::firstOrFail()->status);
    }

    public function test_no_consent_is_logged_as_skipped(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, [
            'sent' => false,
            'reason' => 'no-consent',
        ]))->assertCreated();

        $this->assertSame(ConversionStatus::Skipped, ConversionLog::firstOrFail()->status);
    }

    public function test_not_configured_is_logged_as_skipped(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, [
            'sent' => false,
            'reason' => 'not-configured',
        ]))->assertCreated();

        $this->assertSame(ConversionStatus::Skipped, ConversionLog::firstOrFail()->status);
    }

    public function test_blocked_tracker_is_logged_as_failed(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, [
            'sent' => false,
            'reason' => 'gtag-not-loaded',
        ]))->assertCreated();

        $this->assertSame(ConversionStatus::Failed, ConversionLog::firstOrFail()->status);
    }

    public function test_client_supplied_value_is_ignored(): void
    {
        $order = $this->neutralOrder(['total' => 10.00]);

        $this->postJson('/checkout/conversion', $this->payload($order, ['value' => 99999]))
            ->assertCreated();

        $this->assertEquals(10.00, (float) ConversionLog::firstOrFail()->value);
    }

    public function test_unknown_platform_is_rejected(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, ['platform' => 'pinterest']))
            ->assertUnprocessable();

        $this->assertSame(0, ConversionLog::count());
    }

    public function test_unknown_order_is_rejected(): void
    {
        Order::factory()->create();

        $this->postJson('/checkout/conversion', $this->payload(Order::first(), ['order' => 'NOPE123456']))
            ->assertUnprocessable();

        $this->assertSame(0, ConversionLog::count());
    }

    public function test_facebook_pixel_platform_is_accepted(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, ['platform' => 'facebook_pixel']))
            ->assertCreated();

        $this->assertSame(ConversionPlatform::FacebookPixel, ConversionLog::firstOrFail()->platform);
    }

    public function test_non_http_url_is_rejected(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, [
            'url' => 'javascript:alert(document.cookie)',
        ]))->assertUnprocessable();

        $this->assertSame(0, ConversionLog::count());
    }

    public function test_sent_reason_is_normalized_server_side(): void
    {
        $order = $this->neutralOrder();

        $this->postJson('/checkout/conversion', $this->payload($order, ['reason' => 'totally-custom']))
            ->assertCreated();

        $this->assertSame('sent', ConversionLog::firstOrFail()->reason);
    }

    public function test_logs_are_capped_per_order_and_platform(): void
    {
        $order = $this->neutralOrder();
        ConversionLog::factory()->count(10)->create([
            'order_id' => $order->id,
            'platform' => ConversionPlatform::GoogleAds,
        ]);

        $this->postJson('/checkout/conversion', $this->payload($order))
            ->assertStatus(429);

        $this->assertSame(10, ConversionLog::count());

        // Other platforms for the same order are unaffected by the cap.
        $this->postJson('/checkout/conversion', $this->payload($order, ['platform' => 'tiktok']))
            ->assertCreated();
    }

    public function test_cross_platform_logs_are_dropped_for_a_paid_source(): void
    {
        // A Google-Ads order (gclid + utm_source=google).
        $order = Order::factory()->create([
            'source' => 'google', 'medium' => 'cpc', 'gclid' => 'abc123', 'fbclid' => null, 'ttclid' => null,
        ]);

        // Facebook / TikTok must not log for a Google-Ads order.
        $this->postJson('/checkout/conversion', $this->payload($order, ['platform' => 'facebook_pixel']))
            ->assertOk()
            ->assertJson(['logged' => false, 'reason' => 'not-eligible']);
        $this->assertSame(0, ConversionLog::count());

        // The matching Google Ads pixel still logs normally.
        $this->postJson('/checkout/conversion', $this->payload($order, ['platform' => 'google_ads']))
            ->assertCreated();
        $this->assertSame(1, ConversionLog::count());
    }
}
