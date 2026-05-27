<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Revenue (paid orders per day, 30d)';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $orders = Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total']);

        $buckets = [];
        for ($i = 0; $i < 30; $i++) {
            $buckets[$start->copy()->addDays($i)->format('Y-m-d')] = 0.0;
        }

        foreach ($orders as $order) {
            $key = $order->created_at->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key] += (float) $order->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => array_map(fn (float $v): float => round($v, 2), array_values($buckets)),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => array_map(
                fn (string $date): string => Carbon::parse($date)->format('M j'),
                array_keys($buckets),
            ),
        ];
    }
}
