<?php

namespace App\Services\Geo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resolves a 2-letter ISO country code from an IP address (free ip-api.com),
 * cached per IP. Never throws and never blocks for long — returns null for
 * private/invalid IPs or on any lookup failure.
 */
class IpCountry
{
    public function lookup(?string $ip): ?string
    {
        // Skip blanks and private/reserved ranges (localhost, LAN, etc.).
        if (blank($ip) || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        return Cache::remember('ip_country:'.$ip, now()->addDays(30), function () use ($ip): ?string {
            try {
                $response = Http::timeout(2)->get('http://ip-api.com/json/'.$ip, [
                    'fields' => 'status,countryCode',
                ]);

                if ($response->ok() && $response->json('status') === 'success') {
                    $code = strtoupper((string) $response->json('countryCode'));

                    return strlen($code) === 2 ? $code : null;
                }
            } catch (\Throwable $e) {
                Log::warning('IP country lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            }

            return null;
        });
    }
}
