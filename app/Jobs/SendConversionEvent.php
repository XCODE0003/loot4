<?php

namespace App\Jobs;

use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Services\Conversions\ConversionDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Delivers a single conversion event to its platform and records the outcome.
 */
class SendConversionEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $conversionLogId) {}

    public function handle(ConversionDispatcher $dispatcher): void
    {
        $log = ConversionLog::find($this->conversionLogId);

        if (! $log) {
            return;
        }

        try {
            $response = $dispatcher->send($log);

            $log->update([
                'status' => ConversionStatus::Sent,
                'response_payload' => $response,
                'reason' => null,
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => ConversionStatus::Failed,
                'reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
