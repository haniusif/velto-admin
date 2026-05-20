<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Vehicle;
use App\Models\WalletTransaction;
use App\Models\Worker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $customers = Customer::count();
        $newThisWeek = Customer::where('created_at', '>=', now()->subDays(7))->count();
        $workersActive = Worker::where('status', 'active')->count();
        $vehicles = Vehicle::count();
        $unreadNotifs = CustomerNotification::whereNull('read_at')->count();

        $topUpsThisMonth = WalletTransaction::where('kind', 'top_up')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $bookingsThisMonth = WalletTransaction::where('kind', 'booking')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $walletFloat = Customer::sum('wallet_balance');

        // 7-day sparklines
        $newCustomerSpark = $this->dailyCounts(Customer::query(), 7);
        $bookingSpark = $this->dailyCounts(
            WalletTransaction::query()->where('kind', 'booking'),
            7
        );
        $topUpSpark = $this->dailySums(
            WalletTransaction::query()->where('kind', 'top_up'),
            7
        );

        return [
            Stat::make(__('Customers'), number_format($customers))
                ->description("+{$newThisWeek} ".__('this week'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($newCustomerSpark),

            Stat::make(__('Active workers'), number_format($workersActive))
                ->description(number_format(Worker::count()).' '.__('total'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('Vehicles'), number_format($vehicles))
                ->description(__('Across all customers'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('gray'),

            Stat::make(__('Bookings this month'), number_format($bookingsThisMonth))
                ->description(__('Last 7 days'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->chart($bookingSpark),

            Stat::make(
                __('Top-ups this month'),
                number_format($topUpsThisMonth, 2).' SAR'
            )
                ->description(__('Last 7 days'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($topUpSpark),

            Stat::make(__('Wallet float'), number_format($walletFloat, 2).' SAR')
                ->description(__('All customer balances'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make(__('Unread notifications'), number_format($unreadNotifs))
                ->description(__('Across all customers'))
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($unreadNotifs > 0 ? 'danger' : 'success'),
        ];
    }

    /** Last N days of row counts, ordered oldest → newest. */
    private function dailyCounts($query, int $days): array
    {
        $rows = (clone $query)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d')
            ->pluck('c', 'd')
            ->all();

        return $this->fillDays($rows, $days);
    }

    /** Last N days of summed amounts. */
    private function dailySums($query, int $days): array
    {
        $rows = (clone $query)
            ->selectRaw('DATE(created_at) as d, SUM(amount) as c')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d')
            ->pluck('c', 'd')
            ->map(fn ($v) => (float) $v)
            ->all();

        return $this->fillDays($rows, $days);
    }

    private function fillDays(array $rows, int $days): array
    {
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $out[] = (float) ($rows[$d] ?? 0);
        }
        return $out;
    }
}
