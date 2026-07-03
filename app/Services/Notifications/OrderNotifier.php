<?php

namespace App\Services\Notifications;

use App\Mail\AbandonedCartMail;
use App\Mail\NewOrderMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderDeliveredMail;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Conversions\ConversionLogger;
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

        // Guarantee a Conversion Logs row for ad-sourced sales — best-effort, so a
        // logging hiccup never blocks the payment notification flow.
        try {
            app(ConversionLogger::class)->seedPendingForPaidOrder($order);
        } catch (\Throwable $e) {
            Log::warning('Conversion log seeding failed', ['order' => $order->order_number, 'message' => $e->getMessage()]);
        }

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

    /**
     * A delivered order: confirm delivery to the customer.
     */
    public function delivered(Order $order): void
    {
        $order->loadMissing('items');

        $this->mailTo($order->email, fn () => new OrderDeliveredMail($order), 'customer delivered');
    }

    /**
     * An order left unpaid: remind the customer with a link to complete payment.
     */
    public function abandoned(Order $order): void
    {
        $order->loadMissing('items');

        $this->mailTo($order->email, fn () => new AbandonedCartMail($order), 'abandoned cart');
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
            '💎 Payment: '.$this->paymentLabel($order),
        ];

        // Show the chosen delivery method (skip the plain free "standard" default,
        // which carries no useful signal for staff).
        if ($this->hasChosenDelivery($order)) {
            $fee = (float) $order->delivery_fee;
            $lines[] = '🚚 Delivery: '.$order->delivery_method
                .($fee > 0 ? ' ('.number_format($fee, 2).' '.$order->currency.')' : ' (free)');
        }

        $lines[] = '';
        $lines[] = '🛒 Items Purchased:';

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
     * One string per chosen option, e.g. "Select platform: PlayStation 5",
     * each rendered on its own line in the Telegram message.
     *
     * @return list<string>
     */
    private function itemDetails($item): array
    {
        return array_map(
            fn (array $line): string => $line['label'] !== null
                ? $line['label'].': '.$line['value']
                : $line['value'],
            $item->detailLines(),
        );
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

    /**
     * Whether the order has a meaningful delivery choice worth showing to staff —
     * a paid option, or a named option other than the plain free "standard".
     */
    private function hasChosenDelivery(Order $order): bool
    {
        return (float) $order->delivery_fee > 0
            || (filled($order->delivery_method) && strtolower((string) $order->delivery_method) !== 'standard');
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
