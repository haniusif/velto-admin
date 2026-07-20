<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard banner that reminds the admin to install the cron entry that runs
 * Laravel's scheduler. It appears only when the scheduler heartbeat is missing
 * or stale — so it disappears on its own once cron is correctly configured.
 */
class SchedulerHealthAlert extends Widget
{
    protected string $view = 'filament.widgets.scheduler-health-alert';

    protected static ?int $sort = -10; // pin to the very top of the dashboard

    protected int|string|array $columnSpan = 'full';

    /** Consider the scheduler down if it hasn't checked in for 15 minutes. */
    private const STALE_AFTER_MINUTES = 15;

    public static function canView(): bool
    {
        return static::schedulerIsStale();
    }

    private static function lastRun(): ?Carbon
    {
        $ts = Cache::get('velto.scheduler.last_run');

        return $ts ? Carbon::createFromTimestamp($ts) : null;
    }

    private static function schedulerIsStale(): bool
    {
        $last = static::lastRun();

        return $last === null || $last->lt(now()->subMinutes(self::STALE_AFTER_MINUTES));
    }

    protected function getViewData(): array
    {
        $last = static::lastRun();

        return [
            'lastRun' => $last,
            'cron' => '* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1',
        ];
    }
}
