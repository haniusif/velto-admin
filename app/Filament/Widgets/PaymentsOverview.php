<?php

namespace App\Filament\Widgets;

use App\Models\PaymentTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();

        $captured = (float) PaymentTransaction::where('status', PaymentTransaction::STATUS_CAPTURED)
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $refunds = (float) PaymentTransaction::where('status', PaymentTransaction::STATUS_REFUNDED)
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $topups = (float) PaymentTransaction::where('purpose', 'wallet_topup')
            ->where('status', PaymentTransaction::STATUS_CAPTURED)
            ->where('created_at', '>=', $monthStart)->sum('amount');

        $pending = PaymentTransaction::where('status', PaymentTransaction::STATUS_PENDING)->count();
        $failed = PaymentTransaction::where('status', PaymentTransaction::STATUS_FAILED)
            ->where('created_at', '>=', $monthStart)->count();

        return [
            Stat::make(__('Captured'), number_format($captured, 2).' SAR')
                ->description(__('this month'))
                ->color('success'),
            Stat::make(__('Refunds'), number_format($refunds, 2).' SAR')
                ->description(__('this month'))
                ->color('warning'),
            Stat::make(__('Top-ups'), number_format($topups, 2).' SAR')
                ->description(__('this month'))
                ->color('primary'),
            Stat::make(__('Pending'), (string) $pending)
                ->description(__('Failed').': '.$failed)
                ->color($pending > 0 ? 'warning' : 'gray'),
        ];
    }
}
