<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Appointment;
use App\Models\Customer;
use App\Support\BookingTime;
use App\Support\CustomerProfile;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('phone')->label(__('Phone'))->copyable(),
                        TextEntry::make('email')->label(__('Email'))->placeholder('—')->copyable(),
                        TextEntry::make('city')->label(__('City')),
                        TextEntry::make('preferred_language')
                            ->label(__('Preferred language'))
                            ->formatStateUsing(fn (string $state): string => strtoupper($state))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'blocked' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                    ]),

                // What this customer is worth and how dependable they are —
                // the questions asked before offering a plan, a refund or a
                // goodwill wash.
                Section::make(__('Value'))
                    ->columns(4)
                    ->components([
                        TextEntry::make('lifetime_spend')
                            ->label(__('Lifetime spend'))
                            ->state(fn (Customer $record): float => CustomerProfile::for($record)->spend)
                            ->money('SAR')
                            ->weight('bold')
                            ->helperText(__('Completed visits only')),
                        TextEntry::make('average_order')
                            ->label(__('Average visit'))
                            ->state(fn (Customer $record): ?float => CustomerProfile::for($record)->averageOrder())
                            ->money('SAR')
                            ->placeholder('—'),
                        TextEntry::make('wallet_balance')
                            ->label(__('Wallet balance'))
                            ->money('SAR')
                            ->color(fn (Customer $record): string => $record->wallet_balance > 0 ? 'success' : 'gray'),
                        TextEntry::make('active_plans')
                            ->label(__('Active plans'))
                            ->state(fn (Customer $record): int => CustomerProfile::for($record)->activePlans)
                            ->badge()
                            ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
                    ]),

                Section::make(__('Reliability'))
                    ->columns(4)
                    ->components([
                        TextEntry::make('total_bookings')
                            ->label(__('Bookings'))
                            ->state(fn (Customer $record): int => CustomerProfile::for($record)->bookings)
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('completed_bookings')
                            ->label(__('Completed'))
                            ->state(fn (Customer $record): int => CustomerProfile::for($record)->completed)
                            ->badge()
                            ->color('success'),
                        TextEntry::make('cancelled_bookings')
                            ->label(__('Cancelled'))
                            ->state(fn (Customer $record): int => CustomerProfile::for($record)->cancelled)
                            ->badge()
                            ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
                        TextEntry::make('cancellation_rate')
                            ->label(__('Cancellation rate'))
                            ->state(function (Customer $record): ?string {
                                $rate = CustomerProfile::for($record)->cancellationRate();

                                return $rate === null ? null : "{$rate}%";
                            })
                            ->placeholder(__('No bookings yet'))
                            ->badge()
                            ->color(function (Customer $record): string {
                                $rate = CustomerProfile::for($record)->cancellationRate();

                                return match (true) {
                                    $rate === null => 'gray',
                                    $rate >= 40 => 'danger',
                                    $rate >= 20 => 'warning',
                                    default => 'success',
                                };
                            }),
                    ]),

                Section::make(__('Visits'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('last_visit')
                            ->label(__('Last visit'))
                            ->state(fn (Customer $record): ?string => self::visitLabel(
                                CustomerProfile::for($record)->lastVisit,
                            ))
                            ->placeholder(__('Never')),
                        TextEntry::make('next_visit')
                            ->label(__('Next visit'))
                            ->state(fn (Customer $record): ?string => self::visitLabel(
                                CustomerProfile::for($record)->nextVisit,
                            ))
                            ->placeholder(__('Nothing booked')),
                    ]),

                Section::make(__('Engagement'))
                    ->columns(4)
                    ->components([
                        TextEntry::make('promo_uses')
                            ->label(__('Promo codes used'))
                            ->state(fn (Customer $record): int => CustomerProfile::for($record)->promoRedemptions)
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('promo_discount')
                            ->label(__('Discount given'))
                            ->state(fn (Customer $record): float => CustomerProfile::for($record)->promoDiscount)
                            ->money('SAR'),
                        TextEntry::make('devices')
                            ->label(__('App installed'))
                            // A customer with no registered device cannot be
                            // reached by push, however many notifications the
                            // admin sends them.
                            ->state(function (Customer $record): string {
                                $platforms = $record->devices()->pluck('platform')->unique();

                                return $platforms->isEmpty()
                                    ? __('No device')
                                    : $platforms->map(fn ($p) => strtoupper((string) $p))->join(', ');
                            })
                            ->badge()
                            ->color(fn (Customer $record): string => $record->devices()->exists() ? 'success' : 'danger'),
                        TextEntry::make('vehicles_count')
                            ->label(__('Cars'))
                            ->state(fn (Customer $record): int => $record->vehicles()->count())
                            ->badge()
                            ->color('gray'),
                    ]),

                Section::make(__('Activity'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('joined_at')->label(__('Joined at'))->dateTime('Y-m-d H:i')->placeholder('—'),
                        TextEntry::make('created_at')->label(__('Created'))->dateTime('Y-m-d H:i'),
                    ]),

                Section::make(__('Notes'))
                    ->components([
                        TextEntry::make('notes')->label(__('Notes'))->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }

    /** "2026-08-19 6:30 PM · Express exterior wash", or null when there is none. */
    private static function visitLabel(?Appointment $appointment): ?string
    {
        if ($appointment === null) {
            return null;
        }

        // scheduled_at holds Riyadh wall-clock digits stored naively, so it is
        // printed, never converted.
        $when = BookingTime::wallClockLabel($appointment->scheduled_at, arabic: app()->getLocale() === 'ar');

        return trim(($when ?? '').' · '.$appointment->service_name, ' ·');
    }
}
