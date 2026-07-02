<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\TimeSlot;
use Filament\Widgets\ChartWidget;

class BookingActivityChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return __('Slot capacity vs bookings');
    }

    public function getDescription(): ?string
    {
        return __('Next 7 days');
    }

    protected function getData(): array
    {
        $labels = [];
        $capacity = [];
        $booked = [];

        for ($i = 0; $i < 7; $i++) {
            $day = now()->startOfDay()->addDays($i);
            $labels[] = $day->format('D · M d');

            $cap = TimeSlot::whereDate('date', $day->toDateString())->sum('capacity');

            $capacity[] = (int) $cap;
            $booked[] = (int) Appointment::whereDate('scheduled_at', $day->toDateString())
                ->where('status', '!=', 'cancelled')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('Capacity'),
                    'data' => $capacity,
                    'backgroundColor' => 'rgba(136,99,229,0.30)',
                    'borderColor' => '#8863E5',
                    'borderWidth' => 1,
                ],
                [
                    'label' => __('Booked'),
                    'data' => $booked,
                    'backgroundColor' => '#8863E5',
                    'borderColor' => '#8863E5',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
