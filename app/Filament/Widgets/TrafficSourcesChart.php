<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class TrafficSourcesChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Traffic sources';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Order::query()
            ->whereNotNull('source')
            ->get(['source'])
            ->groupBy('source')
            ->map(fn ($group): int => $group->count())
            ->sortDesc();

        $palette = ['#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ef4444', '#6b7280', '#ec4899'];

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $counts->values()->all(),
                    'backgroundColor' => array_slice($palette, 0, $counts->count() ?: 1),
                ],
            ],
            'labels' => $counts->keys()->map(fn ($s): string => ucfirst((string) $s))->all(),
        ];
    }
}
