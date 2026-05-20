<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;

class CustomersByCityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return __('Customers by city');
    }

    protected function getData(): array
    {
        $rows = Customer::query()
            ->selectRaw('COALESCE(city, "—") as city, COUNT(*) as c')
            ->groupBy('city')
            ->orderByDesc('c')
            ->limit(6)
            ->pluck('c', 'city')
            ->all();

        return [
            'datasets' => [[
                'label' => __('Customers'),
                'data' => array_values($rows),
                'backgroundColor' => [
                    '#8863E5', // velto purple
                    '#B38BEE',
                    '#CBB5F3',
                    '#C9E3DA',
                    '#E0A86A',
                    '#6B6580',
                ],
                'borderWidth' => 0,
            ]],
            'labels' => array_keys($rows),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
            'cutout' => '60%',
        ];
    }
}
