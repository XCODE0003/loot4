<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sales (orders per day, 30d)';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $orders = Order::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at']);

        $buckets = [];
        for ($i = 0; $i < 30; $i++) {
            $buckets[$start->copy()->addDays($i)->format('Y-m-d')] = 0;
        }

        foreach ($orders as $order) {
            $key = $order->created_at->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key]++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_values($buckets),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_map(
                fn (string $date): string => \Illuminate\Support\Carbon::parse($date)->format('M j'),
                array_keys($buckets),
            ),
        ];
    }
}
