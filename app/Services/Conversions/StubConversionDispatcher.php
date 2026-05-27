<?php

namespace App\Services\Conversions;

use App\Models\ConversionLog;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder dispatcher used until real platform SDKs are wired up.
 *
 * It records the intent and returns a synthetic "accepted" response so the
 * rest of the pipeline (statuses, payloads, retries) is fully functional.
 * Swap the container binding in AppServiceProvider for real implementations
 * (FacebookCapiDispatcher, TikTokEventsDispatcher, …) without touching callers.
 */
class StubConversionDispatcher implements ConversionDispatcher
{
    public function send(ConversionLog $log): array
    {
        Log::info('Conversion event dispatched (stub)', [
            'platform' => $log->platform->value,
            'event' => $log->event,
            'order_id' => $log->order_id,
            'value' => $log->value,
        ]);

        return [
            'stub' => true,
            'platform' => $log->platform->value,
            'event' => $log->event,
            'events_received' => 1,
            'received_at' => now()->toIso8601String(),
        ];
    }
}
