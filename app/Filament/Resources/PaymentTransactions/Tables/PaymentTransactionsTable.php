<?php

namespace App\Filament\Resources\PaymentTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('When'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appointment_id')
                    ->label(__('Order'))
                    ->prefix('#')
                    ->placeholder('—'),

                TextColumn::make('gateway')
                    ->label(__('Gateway'))
                    ->badge(),

                TextColumn::make('action')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'refund' ? 'warning' : 'primary'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'captured' => 'success',
                        'pending' => 'gray',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('track_id')
                    ->label(__('Track ID'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('trans_id')
                    ->label(__('Transaction ID'))
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('ref')
                    ->label(__('Ref (RRN)'))
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'captured' => __('Captured'),
                        'failed' => __('Failed'),
                        'refunded' => __('Refunded'),
                    ]),
                SelectFilter::make('gateway')
                    ->label(__('Gateway'))
                    ->options(['arb' => 'ARB / Neoleap']),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
