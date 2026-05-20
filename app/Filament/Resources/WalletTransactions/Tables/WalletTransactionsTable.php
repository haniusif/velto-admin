<?php

namespace App\Filament\Resources\WalletTransactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsTable
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

                TextColumn::make('kind')
                    ->label(__('Kind'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'top_up' => 'success',
                        'booking' => 'primary',
                        'refund' => 'warning',
                        'adjustment' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'top_up' => __('Top-up'),
                        'booking' => __('Booking'),
                        'refund' => __('Refund'),
                        'adjustment' => __('Adjustment'),
                        default => $state,
                    }),

                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('SAR')
                    ->color(fn ($record) => $record->amount >= 0 ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('note')
                    ->label(__('Note'))
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label(__('Kind'))
                    ->options([
                        'top_up' => __('Top-up'),
                        'booking' => __('Booking'),
                        'refund' => __('Refund'),
                        'adjustment' => __('Adjustment'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
