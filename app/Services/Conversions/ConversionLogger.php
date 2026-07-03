<?php

namespace App\Services\Conversions;

use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;

/**
 * Records the Google Ads Purchase conversion for every paid sale so the admin
 * "Conversion Logs" page shows one "Sent" row per order — the way BroLooting's
 * does.
 *
 * On production the conversion itself is fired by Google Tag Manager off the
 * success page's `dataLayer` `purchase` event (no native gtag pixel ID is set),
 * so the browser's native-pixel path never reports anything and the log would
 * otherwise stay empty. We therefore write the authoritative "Sent" row here,
 * server-side, the moment the order is marked paid — immune to adblock, stale
 * cached bundles or a closed tab.
 */
class ConversionLogger
{
    /**
     * Log the Purchase conversion for a paid order. Idempotent per order: a
     * duplicate paid() call (e.g. a repeated webhook or a manual re-mark in the
     * admin) never adds a second row. Source-agnostic — the GTM Google Ads
     * conversion fires on every purchase and Google attributes it to a click
     * only when a gclid is present, so every paid sale gets exactly one row.
     */
    public function logPaidOrderConversion(Order $order): void
    {
        ConversionLog::firstOrCreate(
            [
                'order_id' => $order->id,
                'platform' => 'google_ads',
                'event' => 'Purchase',
            ],
            [
                'value' => $order->total,
                'currency' => $order->currency,
                'status' => ConversionStatus::Sent,
                'reason' => 'sent',
                'url' => route('checkout.success', $order->order_number),
                'sent_at' => now(),
                // Marks server-originated rows: the "Retry" action is hidden for
                // these (a GTM pixel cannot be re-fired from the server).
                'request_payload' => ['origin' => 'server', 'mechanism' => 'gtm'],
            ],
        );
    }
}
