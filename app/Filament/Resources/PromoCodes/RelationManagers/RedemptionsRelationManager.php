<?php

namespace App\Filament\Resources\PromoCodes\RelationManagers;

use App\Models\Appointment;
use App\Models\PromoCodeRedemption;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Who used this code, and on which booking.
 *
 * Read-only: a redemption is the record of something that already happened —
 * money was discounted and a usage counted against the code. Editing it here
 * would desync `used_count` and the booking's totals.
 */
class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Customers who used this code');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->redemptions()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('created_at')->timezone(config('app.timezone'))
                    ->label(__('When'))
                    ->dateTime('Y-m-d g:i A')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->url(fn (PromoCodeRedemption $r): ?string => $r->customer_id
                        ? route('filament.admin.resources.customers.view', ['record' => $r->customer_id])
                        : null),

                TextColumn::make('customer.phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                // The booking number the discount was actually spent on.
                TextColumn::make('appointment_id')
                    ->label(__('Booking'))
                    ->prefix('#')
                    ->placeholder(__('Booking deleted'))
                    ->searchable()
                    ->url(fn (PromoCodeRedemption $r): ?string => $r->appointment_id
                        ? route('filament.admin.resources.appointments.view', ['record' => $r->appointment_id])
                        : null),

                TextColumn::make('appointment.status')
                    ->label(__('Booking status'))
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?string $state): string => match ($state) {
                        Appointment::STATUS_COMPLETED => 'success',
                        Appointment::STATUS_CANCELLED => 'danger',
                        Appointment::STATUS_PENDING => 'warning',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : __(ucfirst(str_replace('_', ' ', $state)))),

                TextColumn::make('amount')
                    ->label(__('Discount'))
                    ->money('SAR')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label(__('Total'))->money('SAR')),

                TextColumn::make('appointment.total_price')
                    ->label(__('Booking total'))
                    ->money('SAR')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('Not used yet'))
            ->emptyStateDescription(__('Redemptions appear here as customers book with this code.'));
    }
}
