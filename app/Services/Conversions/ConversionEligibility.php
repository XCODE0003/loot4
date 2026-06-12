<?php

namespace App\Services\Conversions;

use App\Models\Order;

/**
 * Decides which browser ad-pixels may fire a Purchase conversion for an order,
 * based on its traffic source. A Google-Ads order (gclid / utm_source=google)
 * should only fire the Google Ads pixel — not Facebook or TikTok — so the
 * Conversion Logs stay clean and attribution is never double-counted.
 */
class ConversionEligibility
{
    /**
     * Frontend pixel platform keys this order is allowed to fire, derived from
     * its click IDs (gclid / fbclid / ttclid) and utm source.
     *
     * Returns null when no paid-ad source is detected (organic / referral /
     * direct) — meaning "no restriction": every configured pixel may fire, as
     * before. Returns a restricted list as soon as a paid source is matched.
     *
     * @return list<string>|null
     */
    public static function for(Order $order): ?array
    {
        $source = strtolower((string) ($order->source ?? ''));
        $platforms = [];

        if (filled($order->gclid) || str_contains($source, 'google')) {
            $platforms[] = 'google_ads';
        }

        if (filled($order->fbclid)
            || str_contains($source, 'facebook')
            || str_contains($source, 'instagram')
            || str_contains($source, 'meta')
            || in_array($source, ['fb', 'ig'], true)) {
            $platforms[] = 'facebook_pixel';
        }

        if (filled($order->ttclid) || str_contains($source, 'tiktok')) {
            $platforms[] = 'tiktok';
        }

        return $platforms === [] ? null : array_values(array_unique($platforms));
    }
}
