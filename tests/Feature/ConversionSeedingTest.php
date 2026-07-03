<?php

namespace Tests\Feature;

use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;
use App\Services\Notifications\OrderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A paid sale that came from a paid-ad source must always leave a Conversion
 * Logs row (Pending), even if the customer's browser never fires/reports the
 * pixel. The success-page client then confirms that row as Sent/Failed.
 */
class ConversionSeedingTest extends TestCase
{
    use RefreshDatabase;

    private function markPaid(Order $order): void
    {
        app(OrderNotifier::class)->paid($order);
    }

    public function test_paid_ad_order_seeds_a_pending_row_for_its_platform(): void
    {
        $order = Order::factory()->create([
            'source' => 'google', 'gclid' => 'abc123', 'fbclid' => null, 'ttclid' => null,
            'total' => 42.00, 'currency' => 'USD',
        ]);

        $this->markPaid($order);

        $logs = ConversionLog::where('order_id', $order->id)->get();
        $this->assertCount(1, $logs, 'exactly one row for the google source');
        $log = $logs->first();
        $this->assertSame('google_ads', $log->platform->value);
        $this->assertSame(ConversionStatus::Pending, $log->status);
        $this->assertEquals(42.00, (float) $log->value);
        $this->assertSame('server', $log->request_payload['origin'] ?? null);
    }

    public function test_paid_organic_order_seeds_nothing(): void
    {
        $order = Order::factory()->create([
            'source' => 'direct', 'medium' => 'none', 'gclid' => null, 'fbclid' => null, 'ttclid' => null,
        ]);

        $this->markPaid($order);

        $this->assertSame(0, ConversionLog::where('order_id', $order->id)->count());
    }

    public function test_seeding_is_idempotent_across_repeated_paid_calls(): void
    {
        $order = Order::factory()->create(['source' => 'facebook', 'fbclid' => 'fb1', 'gclid' => null, 'ttclid' => null]);

        $this->markPaid($order);
        $this->markPaid($order); // e.g. a duplicate webhook

        $this->assertSame(1, ConversionLog::where('order_id', $order->id)->where('platform', 'facebook_pixel')->count());
    }

    public function test_client_send_confirms_the_seeded_pending_row_in_place(): void
    {
        $order = Order::factory()->create([
            'source' => 'google', 'gclid' => 'g1', 'fbclid' => null, 'ttclid' => null, 'total' => 30,
        ]);
        $this->markPaid($order);

        $this->postJson('/checkout/conversion', [
            'order' => $order->order_number,
            'platform' => 'google_ads',
            'event' => 'Purchase',
            'sent' => true,
            'reason' => 'sent',
        ])->assertOk()->assertJson(['logged' => true]);

        $logs = ConversionLog::where('order_id', $order->id)->where('platform', 'google_ads')->get();
        $this->assertCount(1, $logs, 'confirmed in place, no duplicate row');
        $this->assertSame(ConversionStatus::Sent, $logs->first()->status);
        $this->assertNotNull($logs->first()->sent_at);
    }

    public function test_no_consent_skip_leaves_the_seeded_row_pending_and_visible(): void
    {
        $order = Order::factory()->create([
            'source' => 'google', 'gclid' => 'g2', 'fbclid' => null, 'ttclid' => null,
        ]);
        $this->markPaid($order);

        // A "no-consent" skip must not flip a real sale to a hidden Skipped row.
        $this->postJson('/checkout/conversion', [
            'order' => $order->order_number,
            'platform' => 'google_ads',
            'event' => 'Purchase',
            'sent' => false,
            'reason' => 'no-consent',
        ])->assertOk();

        $logs = ConversionLog::where('order_id', $order->id)->where('platform', 'google_ads')->get();
        $this->assertCount(1, $logs);
        $this->assertSame(ConversionStatus::Pending, $logs->first()->status);
    }
}
