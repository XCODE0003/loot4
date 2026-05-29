<?php

namespace App\Services\Notifications;

use App\Mail\NewOrderMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Orchestrates customer-facing and internal notifications for an order:
 * the "new order" Telegram message, the "order failed" Telegram message,
 * the customer confirmation email and the staff new-order email.
 *
 * All channels are best-effort: failures are logged, never thrown, so a
 * payment webhook is never broken by a notification problem.
 */
class OrderNotifier
{
    public function __construct(private readonly TelegramNotifier $telegram) {}

    /**
     * A paid order: notify staff (Telegram + email) and confirm to the customer.
     */
    public function paid(Order $order): void
    {
        $order->loadMissing(['items', 'payments']);

        $this->telegram->send(
            Setting::get('telegram_bot_token'),
            Setting::get('telegram_chat_id'),
            $this->newOrderText($order),
        );

        $this->mailTo($order->email, fn () => new OrderConfirmationMail($order), 'customer confirmation');

        $staff = Setting::get('order_notify_email') ?: config('mail.from.address');
        $this->mailTo($staff, fn () => new NewOrderMail($order), 'staff new-order');
    }

    /**
     * A failed / dropped payment: notify staff via the dedicated failed-orders bot
     * (falls back to the main orders bot when not separately configured).
     */
    public function failed(Order $order, ?string $reason = null): void
    {
        $order->loadMissing(['items', 'payments']);

        $this->telegram->send(
            Setting::get('telegram_failed_bot_token') ?: Setting::get('telegram_bot_token'),
            Setting::get('telegram_failed_chat_id') ?: Setting::get('telegram_chat_id'),
            $this->failedOrderText($order, $reason),
        );
    }

    private function newOrderText(Order $order): string
    {
        $lines = [
            '🎮 NEW ORDER RECEIVED',
            '',
            'ℹ️ Order Details',
            '━━━━━━━━━━━━━━━━━━',
            '',
            '⏰ Date: '.$order->created_at?->format('d.m.Y, H:i'),
            '🆔 Order ID: #'.$order->order_number,
            '👤 Customer: '.$order->email,
            '📣 Source: '.$this->sourceLabel($order),
            '💡 Status: Waiting delivery',
            '💎 Payment: '.$this->paymentLabel($order),
            '',
            '🛒 Items Purchased:',
        ];

        foreach ($order->items as $item) {
            $lines[] = ' • '.$item->product_name.' (x'.$item->quantity.')';
            foreach ($this->itemDetails($item) as $detail) {
                $lines[] = '    - '.$detail;
            }
        }

        $lines[] = '➕ Total Amount:';
        $lines[] = number_format((float) $order->total, 2).' '.$order->currency;

        return implode("\n", $lines);
    }

    private function failedOrderText(Order $order, ?string $reason): string
    {
        $lines = [
            '⚠️ ORDER FAILED — PAYMENT DROPPED',
            '',
            'ℹ️ Order Details',
            '━━━━━━━━━━━━━━━━━━',
            '',
            '⏰ Date: '.$order->created_at?->format('d.m.Y, H:i'),
            '🆔 Order ID: #'.$order->order_number,
            '👤 Customer: '.$order->email,
            '💎 Payment: '.$this->paymentLabel($order),
        ];

        if (filled($reason)) {
            $lines[] = '❌ Reason: '.$reason;
        }

        $lines[] = '➕ Total Amount: '.number_format((float) $order->total, 2).' '.$order->currency;

        return implode("\n", $lines);
    }

    /**
     * @return list<string>
     */
    private function itemDetails($item): array
    {
        $data = is_array($item->form_data) ? $item->form_data : [];
        $details = [];

        foreach ($data as $key => $value) {
            if (blank($value) || is_array($value)) {
                continue;
            }

            $details[] = Str::headline((string) $key).': '.$value;
        }

        return $details;
    }

    private function paymentLabel(Order $order): string
    {
        $method = (string) ($order->payments->first()?->method ?? 'card');
        $method = (string) Str::of($method)->replaceFirst('stripe-', '')->upper()->replace('_', '-');

        return 'ICENOX-'.$method;
    }

    private function sourceLabel(Order $order): string
    {
        return Str::headline((string) ($order->source ?? 'storefront'));
    }

    private function mailTo(?string $address, callable $mailable, string $context): void
    {
        if (blank($address)) {
            return;
        }

        try {
            Mail::to($address)->send($mailable());
        } catch (\Throwable $e) {
            Log::warning("Order {$context} email failed", ['message' => $e->getMessage()]);
        }
    }
}
