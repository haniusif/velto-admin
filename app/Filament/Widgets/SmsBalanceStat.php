<?php

namespace App\Filament\Widgets;

use App\Services\JawalySMSService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

/**
 * Remaining SMS credits.
 *
 * Every sign-in costs one message. When the balance runs out 4jawaly keeps
 * answering HTTP 200, so nothing in the app fails loudly — customers simply
 * stop receiving codes and cannot sign in. Putting the number on the dashboard
 * makes that visible before it happens rather than after.
 */
class SmsBalanceStat extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    /** Below this many credits, treat it as needing attention. */
    private const LOW = 200;

    /** The provider is slow and the number moves slowly; don't call it per page load. */
    private const CACHE_MINUTES = 15;

    protected function getStats(): array
    {
        $balance = Cache::remember(
            'sms.balance',
            now()->addMinutes(self::CACHE_MINUTES),
            fn (): array => app(JawalySMSService::class)->balance(),
        );

        if (! ($balance['configured'] ?? false)) {
            return [
                Stat::make(__('SMS credits'), __('Not configured'))
                    ->description(__('No SMS provider credentials'))
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('gray'),
            ];
        }

        if ($balance['error'] !== null || $balance['remaining'] === null) {
            return [
                Stat::make(__('SMS credits'), __('Unavailable'))
                    ->description(__('Could not reach the SMS provider'))
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        $remaining = (int) $balance['remaining'];
        $total = (int) $balance['total'];
        $low = $remaining <= self::LOW;

        $description = $total > 0
            ? __(':remaining of :total remaining', [
                'remaining' => number_format($remaining),
                'total' => number_format($total),
            ])
            : __('No open package');

        if ($balance['expires_at']) {
            $description .= ' · '.__('expires :date', [
                'date' => \Carbon\Carbon::parse($balance['expires_at'])->format('M Y'),
            ]);
        }

        return [
            Stat::make(__('SMS credits'), number_format($remaining))
                ->description($description)
                ->descriptionIcon($low ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-chat-bubble-left-right')
                ->color($low ? 'danger' : 'success'),
        ];
    }
}
