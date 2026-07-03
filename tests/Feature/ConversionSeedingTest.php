<?php

namespace Tests\Feature;

use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;
use App\Services\Notifications\OrderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every paid sale must leave exactly one "Sent" Google Ads Purchase row in the
 * Conversion Logs — the GTM conversion that fires on the success page, recorded
 * server-side so the log is never empty even when the browser's native-pixel
 * path reports nothing (GTM-only setup, adblock, or a closed tab).
 */
class ConversionSeedingTest extends TestCase
{
    use RefreshDatabase;

    private function markPaid(Order $order): void
    {
        app(OrderNotifier::class)->paid($order);
    }

    public function test_paid_order_logs_one_sent_google_ads_purchase_row(): void
    {
        $order = Order::factory()->create([
            'source' => 'google', 'gclid' => 'abc123', 'fbclid' => null, 'ttclid' => null,
            'total' => 42.00, 'currency' => 'USD',
        ]);

        $this->markPaid($order);

        $logs = ConversionLog::where('order_id', $order->id)->get();
        $this->assertCount(1, $logs, 'exactly one conversion row per paid order');

        $log = $logs->first();
        $this->assertSame('google_ads', $log->platform->value);
        $this->assertSame('Purchase', $log->event);
        $this->assertSame(ConversionStatus::Sent, $log->status);
        $this->assertSame('sent', $log->reason);
        $this->assertEquals(42.00, (float) $log->value);
        $this->assertSame('USD', $log->currency);
        $this->assertNotNull($log->sent_at);
        $this->assertSame('server', $log->request_payload['origin'] ?? null);
        $this->assertStringContainsString('/checkout/success/'.$order->order_number, (string) $log->url);
    }

    public function test_paid_organic_order_is_logged_too(): void
    {
        // Every paid sale is logged, not just ad-sourced ones — Google attributes
        // the conversion to a click only when a gclid is present.
        $order = Order::factory()->create([
            'source' => 'direct', 'medium' => 'none', 'gclid' => null, 'fbclid' => null, 'ttclid' => null,
        ]);

        $this->markPaid($order);

        $logs = ConversionLog::where('order_id', $order->id)->get();
        $this->assertCount(1, $logs);
        $this->assertSame(ConversionStatus::Sent, $logs->first()->status);
        $this->assertSame('google_ads', $logs->first()->platform->value);
    }

    public function test_logging_is_idempotent_across_repeated_paid_calls(): void
    {
        $order = Order::factory()->create(['source' => 'facebook', 'fbclid' => 'fb1', 'gclid' => null, 'ttclid' => null]);

        $this->markPaid($order);
        $this->markPaid($order); // e.g. a duplicate webhook or manual re-mark

        $this->assertSame(1, ConversionLog::where('order_id', $order->id)->count());
    }

    public function test_client_pixel_report_does_not_duplicate_the_server_row(): void
    {
        $order = Order::factory()->create([
            'source' => 'google', 'gclid' => 'g1', 'fbclid' => null, 'ttclid' => null, 'total' => 30,
        ]);
        $this->markPaid($order);

        // A native Google Ads pixel report (if one is ever configured) collapses
        // into the existing server row instead of adding a duplicate.
        $this->postJson('/checkout/conversion', [
            'order' => $order->order_number,
            'platform' => 'google_ads',
            'event' => 'Purchase',
            'sent' => true,
            'reason' => 'sent',
        ])->assertOk()->assertJson(['logged' => true]);

        $logs = ConversionLog::where('order_id', $order->id)->where('platform', 'google_ads')->get();
        $this->assertCount(1, $logs, 'no duplicate row');
        $this->assertSame(ConversionStatus::Sent, $logs->first()->status);
        $this->assertNotNull($logs->first()->sent_at);
    }

    public function test_a_later_skip_never_downgrades_the_sent_row(): void
    {
        $order = Order::factory()->create([
            'source' => 'google', 'gclid' => 'g2', 'fbclid' => null, 'ttclid' => null,
        ]);
        $this->markPaid($order);

        // A "no-consent" skip arriving after the sale is logged must not flip a
        // real, sent conversion to a hidden Skipped row.
        $this->postJson('/checkout/conversion', [
            'order' => $order->order_number,
            'platform' => 'google_ads',
            'event' => 'Purchase',
            'sent' => false,
            'reason' => 'no-consent',
        ])->assertOk();

        $logs = ConversionLog::where('order_id', $order->id)->where('platform', 'google_ads')->get();
        $this->assertCount(1, $logs);
        $this->assertSame(ConversionStatus::Sent, $logs->first()->status);
    }
}
