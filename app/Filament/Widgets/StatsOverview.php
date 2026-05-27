<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StorageUnitStatus;
use App\Models\Order;
use App\Models\StorageUnit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $periodStart = now()->subDays(30);

        $paid = Order::query()->where('payment_status', PaymentStatus::Paid->value);
        $revenue = (float) (clone $paid)->where('created_at', '>=', $periodStart)->sum('total');
        $salesCount = (clone $paid)->where('created_at', '>=', $periodStart)->count();

        $totalOrders = Order::query()->where('created_at', '>=', $periodStart)->count();
        $paidOrders = (clone $paid)->where('created_at', '>=', $periodStart)->count();
        $conversion = $totalOrders > 0 ? round($paidOrders / $totalOrders * 100, 1) : 0.0;

        $aov = $salesCount > 0 ? $revenue / $salesCount : 0.0;

        $newUsers = User::query()->where('created_at', '>=', $periodStart)->count();
        $activeStock = StorageUnit::query()->where('status', StorageUnitStatus::Available->value)->sum('stock');
        $pending = Order::query()->where('status', OrderStatus::Pending->value)->count();
        $refunds = Order::query()->where('payment_status', PaymentStatus::Refunded->value)->count();

        return [
            Stat::make('Revenue (30d)', '$'.number_format($revenue, 2))
                ->description('Paid orders, last 30 days')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->dailyRevenueSparkline()),
            Stat::make('Sales (30d)', number_format($salesCount))
                ->description('Paid orders count')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make('New users (30d)', number_format($newUsers))
                ->description('Registered, last 30 days')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
            Stat::make('Active stock', number_format((int) $activeStock))
                ->description('Available storage units')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('gray'),
            Stat::make('Conversion', $conversion.'%')
                ->description('Paid / total orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($conversion >= 50 ? 'success' : 'warning'),
            Stat::make('Avg. order value', '$'.number_format($aov, 2))
                ->description('Per paid order')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            Stat::make('Pending orders', number_format($pending))
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),
            Stat::make('Refunds', number_format($refunds))
                ->description('Refunded orders')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color($refunds > 0 ? 'danger' : 'gray'),
        ];
    }

    /**
     * Revenue per day for the last 7 days (sparkline data).
     *
     * @return array<int, float>
     */
    private function dailyRevenueSparkline(): array
    {
        $start = now()->subDays(6)->startOfDay();

        $orders = Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total']);

        $buckets = [];
        for ($i = 0; $i < 7; $i++) {
            $buckets[$start->copy()->addDays($i)->format('Y-m-d')] = 0.0;
        }

        foreach ($orders as $order) {
            $key = $order->created_at->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key] += (float) $order->total;
            }
        }

        return array_values($buckets);
    }
}
