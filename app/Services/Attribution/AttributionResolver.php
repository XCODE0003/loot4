<?php

namespace App\Services\Attribution;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Throwable;

/**
 * Normalizes the raw client-captured attribution payload into order columns.
 *
 * Tracking data must never block a checkout, so this is deliberately lenient:
 * unexpected shapes are dropped, strings are truncated to the column size and
 * a malformed timestamp simply becomes null instead of failing validation.
 */
class AttributionResolver
{
    /**
     * @param  array<string, mixed>  $attribution  raw payload from the storefront
     * @param  string|null  $ownHost  the shop's host, used to ignore self-referrals
     * @return array<string, mixed> attribute values ready for Order::create()
     */
    public function resolve(array $attribution, ?string $ownHost = null): array
    {
        $get = fn (string $key): ?string => $this->cleanString($attribution[$key] ?? null);

        $gclid = $get('gclid');
        $fbclid = $get('fbclid');
        $ttclid = $get('ttclid');
        $referrerHost = $this->externalReferrerHost($get('referrer'), $ownHost);

        // utm_source wins; otherwise infer the platform from its click ID,
        // then fall back to the external referrer host, then "storefront".
        $source = $get('source') ?? match (true) {
            $gclid !== null => 'google',
            $fbclid !== null => 'facebook',
            $ttclid !== null => 'tiktok',
            $referrerHost !== null => $referrerHost,
            default => 'storefront',
        };

        $medium = $get('medium') ?? match (true) {
            $gclid !== null || $fbclid !== null || $ttclid !== null => 'cpc',
            $referrerHost !== null => 'referral',
            default => null,
        };

        return [
            'source' => $source,
            'medium' => $medium,
            'campaign' => $get('campaign'),
            'content' => $get('content'),
            'term' => $get('term'),
            'gclid' => $gclid,
            'fbclid' => $fbclid,
            'ttclid' => $ttclid,
            'landing_page' => $get('landing_page'),
            'first_visit_at' => $this->cleanDate($attribution['first_visit_at'] ?? null),
        ];
    }

    private function cleanString(mixed $value, int $max = 255): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : Str::limit($value, $max, '');
    }

    private function cleanDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->utc();
        } catch (Throwable) {
            return null;
        }
    }

    private function externalReferrerHost(?string $referrer, ?string $ownHost): ?string
    {
        if ($referrer === null) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = Str::lower($host);

        return $ownHost !== null && Str::lower($ownHost) === $host
            ? null
            : Str::limit($host, 255, '');
    }
}
