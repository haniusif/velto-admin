<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\Appointment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A customer's booking history, on their own page.
 *
 * Read-only on purpose: a booking touches slot capacity, wallet balance and
 * promo redemptions, so it is created and amended through the Appointments
 * screen where those rules live, not edited inline here.
 */
class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Bookings');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_name')
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('scheduled_at')
                    ->label(__('When'))
                    // Riyadh wall-clock digits stored naively: shown as
                    // written, never shifted into another zone.
                    ->dateTime('Y-m-d g:i A')
                    ->sortable(),
                TextColumn::make('service_name')->label(__('Service'))->limit(30),
                TextColumn::make('vehicle_label')->label(__('Car'))->placeholder('—')->limit(24),
                TextColumn::make('worker.name')->label(__('Specialist'))->placeholder(__('Unassigned')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Appointment::STATUS_COMPLETED => 'success',
                        Appointment::STATUS_CANCELLED => 'danger',
                        Appointment::STATUS_PENDING => 'warning',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst(str_replace('_', ' ', $state)))),
                TextColumn::make('total_price')->label(__('Total'))->money('SAR')->sortable(),
                TextColumn::make('payment_status')
                    ->label(__('Payment'))
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'paid' ? 'success' : 'gray')
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : __(ucfirst($state))),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('Status'))->options([
                    Appointment::STATUS_PENDING => __('Pending'),
                    Appointment::STATUS_CONFIRMED => __('Confirmed'),
                    Appointment::STATUS_COMPLETED => __('Completed'),
                    Appointment::STATUS_CANCELLED => __('Cancelled'),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
