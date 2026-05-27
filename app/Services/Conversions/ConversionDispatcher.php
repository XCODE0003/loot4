<?php

namespace App\Services\Conversions;

use App\Models\ConversionLog;

/**
 * Contract for delivering a conversion event to an external platform
 * (Facebook CAPI, TikTok Events, Google Ads, Snapchat, custom webhook…).
 *
 * Implementations return the raw response payload, or throw on failure.
 */
interface ConversionDispatcher
{
    /**
     * Send the conversion event and return the platform response payload.
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException when the platform rejects the event
     */
    public function send(ConversionLog $log): array;
}
