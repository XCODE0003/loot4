<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchExchangeRates implements ShouldQueue
{
    use Queueable;

    /** Currencies to track (all relative to USD). */
    private const CURRENCIES = ['EUR', 'GBP', 'CAD', 'NZD', 'AUD', 'AED', 'SAR'];

    public function handle(): void
    {
        $response = Http::timeout(15)
            ->get('https://open.er-api.com/v6/latest/USD');

        if (! $response->successful()) {
            Log::warning('FetchExchangeRates: HTTP error', ['status' => $response->status()]);

            return;
        }

        $data = $response->json();

        if (($data['result'] ?? '') !== 'success') {
            Log::warning('FetchExchangeRates: unexpected response', ['data' => $data]);

            return;
        }

        $rates = collect(self::CURRENCIES)
            ->mapWithKeys(fn (string $code): array => [
                $code => round((float) ($data['rates'][$code] ?? 1.0), 6),
            ])
            ->all();

        Cache::put('exchange_rates', $rates, now()->addHours(3));

        Log::info('FetchExchangeRates: rates updated', $rates);
    }
}
