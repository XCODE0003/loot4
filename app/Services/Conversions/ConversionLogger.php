<?php

namespace App\Services\Conversions;

use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;

/**
 * Guarantees a Conversion Logs row for every paid sale that came from a paid-ad
 * source. On payment we seed one Pending "Purchase" row per eligible platform,
 * so an ad sale is always visible in the admin even when the customer's browser
 * never fires the pixel or its debug POST is blocked (adblock / Cloudflare /
 * closed tab). The success-page client later confirms the row as Sent/Failed.
 */
class ConversionLogger
{
    /**
     * Ensure a Pending row exists for each platform this order's paid source is
     * eligible to fire. No-ops for organic / direct orders (no paid source →
     * nothing to attribute, so no row is seeded).
     */
    public function seedPendingForPaidOrder(Order $order): void
    {
        $platforms = ConversionEligibility::for($order);

        if ($platforms === null) {
            return;
        }

        foreach ($platforms as $platform) {
            ConversionLog::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'platform' => $platform,
                    'event' => 'Purchase',
                ],
                [
                    'value' => $order->total,
                    'currency' => $order->currency,
                    'status' => ConversionStatus::Pending,
                    'reason' => 'awaiting-pixel',
                    'request_payload' => ['origin' => 'server'],
                ],
            );
        }
    }
}
