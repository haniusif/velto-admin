<?php

namespace App\Filament\Widgets;

use App\Models\AppSetting;
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

        // A checkout is only worth looking at while the customer could still
        // be on the bank's page. Past the grace window the booking has been
        // released and the row is a record of someone who changed their mind —
        // counting those made the tile climb forever and mean nothing. It read
        // 34 while three neighbouring tiles showed the month, and the oldest
        // of the 34 was seven weeks old.
        $graceMinutes = max(1, (int) AppSetting::get('booking.pending_grace_minutes', '30'));
        $cutoff = now()->subMinutes($graceMinutes);

        $inFlight = PaymentTransaction::where('status', PaymentTransaction::STATUS_PENDING)
            ->where('created_at', '>=', $cutoff)->count();

        $abandoned = PaymentTransaction::where('status', PaymentTransaction::STATUS_PENDING)
            ->where('created_at', '<', $cutoff)
            ->where('created_at', '>=', $monthStart)->count();

        $failed = PaymentTransaction::where('status', PaymentTransaction::STATUS_FAILED)
            ->where('created_at', '>=', $monthStart)->count();

        return [
            Stat::make(__('Captured'), number_format($captured, 2).' '.__('SAR'))
                ->description(__('this month'))
                ->color('success'),
            Stat::make(__('Refunds'), number_format($refunds, 2).' '.__('SAR'))
                ->description(__('this month'))
                ->color('warning'),
            Stat::make(__('Top-ups'), number_format($topups, 2).' '.__('SAR'))
                ->description(__('this month'))
                ->color('primary'),
            // Every tile on this card now reads the same month, except the
            // in-flight count, which says so in its own label.
            Stat::make(__('Awaiting payment'), (string) $inFlight)
                ->description(__('Abandoned').": {$abandoned} · ".__('Failed').": {$failed} · ".__('this month'))
                ->color($inFlight > 0 ? 'warning' : 'gray'),
        ];
    }
}
