<?php

namespace App\Filament\Widgets;

use App\Models\WalletTransaction;
use Filament\Widgets\ChartWidget;

class WalletTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('Wallet activity — last 14 days');
    }

    public function getDescription(): ?string
    {
        return __('SAR — top-ups vs booking spend');
    }

    protected function getData(): array
    {
        $days = 14;
        $labels = [];
        $topUps = [];
        $bookings = [];
        $refunds = [];

        $topUpRows = WalletTransaction::query()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as t')
            ->where('kind', 'top_up')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d')
            ->pluck('t', 'd')
            ->map(fn ($v) => (float) $v)
            ->all();

        $bookingRows = WalletTransaction::query()
            ->selectRaw('DATE(created_at) as d, SUM(ABS(amount)) as t')
            ->where('kind', 'booking')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d')
            ->pluck('t', 'd')
            ->map(fn ($v) => (float) $v)
            ->all();

        $refundRows = WalletTransaction::query()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as t')
            ->where('kind', 'refund')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d')
            ->pluck('t', 'd')
            ->map(fn ($v) => (float) $v)
            ->all();

        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $key = $d->toDateString();
            $labels[] = $d->format('M d');
            $topUps[] = (float) ($topUpRows[$key] ?? 0);
            $bookings[] = (float) ($bookingRows[$key] ?? 0);
            $refunds[] = (float) ($refundRows[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Top-ups'),
                    'data' => $topUps,
                    'borderColor' => '#8863E5',
                    'backgroundColor' => 'rgba(136,99,229,0.18)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
                [
                    'label' => __('Bookings'),
                    'data' => $bookings,
                    'borderColor' => '#E0A86A',
                    'backgroundColor' => 'rgba(224,168,106,0.18)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
                [
                    'label' => __('Refunds'),
                    'data' => $refunds,
                    'borderColor' => '#5BB78A',
                    'backgroundColor' => 'rgba(91,183,138,0.18)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
