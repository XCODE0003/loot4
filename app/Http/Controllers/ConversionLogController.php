<?php

namespace App\Http\Controllers;

use App\Enums\ConversionPlatform;
use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;
use App\Services\Conversions\ConversionEligibility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversionLogController extends Controller
{
    /**
     * Reasons the client may legitimately decide not to fire a pixel.
     * Anything else with sent=false is treated as a failure (e.g. blocked script).
     *
     * @var list<string>
     */
    private const SKIP_REASONS = ['localStorage-skip', 'no-consent', 'consent-declined', 'not-configured'];

    /**
     * Skip reasons that carry zero per-order signal and would only clutter the
     * Conversion Logs table: the pixel runs via GTM (no native ID) or the row is
     * a client-side dedup echo. These are NEVER persisted — not even when a
     * stale, cached bundle keeps posting them. Real outcomes (Sent / Failed)
     * and consent skips still get a row.
     *
     * @var list<string>
     */
    private const NOISE_REASONS = ['not-configured', 'localStorage-skip'];

    /**
     * Upper bound of debug rows per order+platform — keeps a public endpoint
     * from flooding the table (the throttle alone can be sidestepped by
     * rotating forwarded IPs).
     */
    private const MAX_LOGS_PER_ORDER_PLATFORM = 10;

    /**
     * Debug log written by the success page for every pixel fire attempt
     * (Google Ads / Facebook / TikTok). Value and currency are re-derived
     * from the order server-side so the client cannot spoof them.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'string', 'max:32', 'exists:orders,order_number'],
            'platform' => ['required', Rule::enum(ConversionPlatform::class)],
            'event' => ['required', 'string', 'max:50'],
            'sent' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'max:100'],
            // http(s) only — a javascript: URI here would become a live link
            // in the admin Conversion Logs table.
            'url' => ['nullable', 'url:http,https', 'max:2000'],
        ]);

        $order = Order::query()->where('order_number', $data['order'])->firstOrFail();

        // Drop cross-platform noise: a Google-Ads order must not log Facebook /
        // TikTok rows (defends against stale cached clients that still post
        // every platform). Null = no paid source → all platforms allowed.
        $eligible = ConversionEligibility::for($order);
        if ($eligible !== null && ! in_array($data['platform'], $eligible, true)) {
            return response()->json(['logged' => false, 'reason' => 'not-eligible'], 200);
        }

        // Drop pure-noise skips before they ever reach the table. The live client
        // already suppresses these, so anything arriving here is a stale/cached
        // bundle — the server is the authoritative gate.
        if (! $data['sent'] && in_array($data['reason'], self::NOISE_REASONS, true)) {
            return response()->json(['logged' => false, 'reason' => 'noise-skipped'], 200);
        }

        $logged = ConversionLog::query()
            ->where('order_id', $order->id)
            ->where('platform', $data['platform'])
            ->count();

        if ($logged >= self::MAX_LOGS_PER_ORDER_PLATFORM) {
            return response()->json(['logged' => false, 'reason' => 'log-cap-reached'], 429);
        }

        // Successful sends always carry the canonical reason; the free-form
        // client string only matters for explaining why nothing was sent.
        $reason = $data['sent'] ? 'sent' : $data['reason'];

        $status = match (true) {
            $data['sent'] => ConversionStatus::Sent,
            in_array($data['reason'], self::SKIP_REASONS, true) => ConversionStatus::Skipped,
            default => ConversionStatus::Failed,
        };

        ConversionLog::create([
            'order_id' => $order->id,
            'platform' => $data['platform'],
            'event' => $data['event'],
            'value' => $order->total,
            'currency' => $order->currency,
            'status' => $status,
            'reason' => $reason,
            'url' => $data['url'] ?? null,
            'sent_at' => $data['sent'] ? now() : null,
            'request_payload' => [
                // Marks browser-originated logs — the admin "Retry" action is
                // hidden for these since a pixel cannot be re-fired server-side.
                'origin' => 'client',
                'client' => $data,
                'user_agent' => (string) $request->userAgent(),
            ],
        ]);

        return response()->json(['logged' => true], 201);
    }
}
