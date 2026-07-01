<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Notifications\OrderNotifier;
use Illuminate\Console\Command;

/**
 * Emails a one-time payment reminder for orders left unpaid past a grace period
 * (default 1 hour). Marks each order so it is never reminded twice, and ignores
 * orders older than a week to avoid spamming a backlog on first run.
 */
class RemindAbandonedOrders extends Command
{
    protected $signature = 'orders:remind-abandoned {--hours=1 : Hours an order may sit unpaid before the reminder}';

    protected $description = 'Email a payment reminder for unpaid orders, once per order.';

    public function handle(OrderNotifier $notifier): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $count = 0;

        Order::query()
            ->where('payment_status', PaymentStatus::Pending)
            ->whereNull('abandoned_reminded_at')
            ->whereNotNull('email')
            ->where('created_at', '<=', now()->subHours($hours))
            ->where('created_at', '>=', now()->subDays(7))
            ->with('items')
            ->chunkById(100, function ($orders) use ($notifier, &$count): void {
                foreach ($orders as $order) {
                    // Guard against a payment that landed since the query ran.
                    if ($order->fresh()?->payment_status !== PaymentStatus::Pending) {
                        continue;
                    }

                    $notifier->abandoned($order);
                    $order->update(['abandoned_reminded_at' => now()]);
                    $count++;
                }
            });

        $this->info("Sent {$count} abandoned-cart reminder(s).");

        return self::SUCCESS;
    }
}
