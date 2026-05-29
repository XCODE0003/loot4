<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin Telegram Bot API transport. Credentials are supplied per call so the
 * caller can use different bot/chat pairs (new orders vs. failed orders).
 */
class TelegramNotifier
{
    public function send(?string $token, ?string $chatId, string $text): bool
    {
        if (blank($token) || blank($chatId)) {
            return false;
        }

        try {
            $response = Http::asJson()
                ->timeout(10)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'disable_web_page_preview' => true,
                ]);

            if (! $response->successful()) {
                Log::warning('Telegram send failed', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram send error', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
