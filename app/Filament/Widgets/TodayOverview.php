<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\TodayAppointments;
use App\Support\BookingTime;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The day's shape, above the day sheet.
 *
 * Built from the panel's own Stat component rather than hand-rolled tiles, so
 * it carries the same card, border, dark-mode and spacing treatment as every
 * other set of figures in the admin — bespoke divs looked close but never
 * quite matched.
 */
class TodayOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // Counted by the page, not recounted here: two copies of "unassigned"
        // would eventually mean two different answers on the same screen.
        $s = TodayAppointments::summary();

        return [
            Stat::make(__('Bookings'), (string) $s['total'])
                ->description(BookingTime::nowWallClock()->translatedFormat('l j F'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('gray'),

            Stat::make(__('Upcoming'), (string) $s['upcoming'])
                ->description($s['in_progress'] > 0
                    ? $s['in_progress'].' '.__('In progress')
                    : __('Nothing under way'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($s['upcoming'] > 0 ? 'primary' : 'gray'),

            Stat::make(__('Unassigned'), (string) $s['unassigned'])
                ->description($s['unassigned'] > 0 ? __('Needs a specialist') : __('All covered'))
                ->descriptionIcon($s['unassigned'] > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($s['unassigned'] > 0 ? 'danger' : 'success'),

            Stat::make(__('Completed'), (string) $s['completed'])
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make(__('Cancelled'), (string) $s['cancelled'])
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($s['cancelled'] > 0 ? 'warning' : 'gray'),

            Stat::make(__('Earned today'), number_format($s['revenue'], 2).' '.__('SAR'))
                ->description(__('completed visits only'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
