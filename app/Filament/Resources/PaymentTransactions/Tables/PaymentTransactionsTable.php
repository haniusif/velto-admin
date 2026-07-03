<?php

namespace App\Filament\Resources\PaymentTransactions\Tables;

use App\Models\PaymentTransaction;
use App\Models\WalletTransaction;
use App\Services\ARB\ArbGateway;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
                Action::make('refund')
                    ->label(__('Refund'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Refund'))
                    ->modalDescription(fn (PaymentTransaction $record): string => __(
                        'Refund :amount SAR to the card via ARB? This cannot be undone.',
                        ['amount' => number_format((float) $record->amount, 2)],
                    ))
                    ->visible(fn (PaymentTransaction $record): bool => $record->gateway === 'arb'
                        && $record->status === PaymentTransaction::STATUS_CAPTURED
                        && filled($record->trans_id))
                    ->action(function (PaymentTransaction $record): void {
                        try {
                            $result = app(ArbGateway::class)->refund(
                                (string) $record->trans_id,
                                (float) $record->amount,
                                $record->track_id,
                            );
                        } catch (\Throwable $e) {
                            Notification::make()->danger()
                                ->title(__('Refund failed'))
                                ->body($e->getMessage())->send();

                            return;
                        }

                        if (! ($result['success'] ?? false)) {
                            Notification::make()->danger()
                                ->title(__('Refund failed'))
                                ->body((string) ($result['result'] ?? 'Gateway declined the refund.'))->send();

                            return;
                        }

                        $record->update(['status' => PaymentTransaction::STATUS_REFUNDED]);
                        $record->appointment?->update(['payment_status' => 'refunded']);

                        // A card top-up refund returns money to the card — reverse the wallet credit.
                        if ($record->purpose === 'wallet_topup') {
                            $record->customer?->walletTransactions()->create([
                                'kind' => WalletTransaction::KIND_ADJUSTMENT,
                                'amount' => -1 * (float) $record->amount,
                                'note' => 'Card top-up refunded',
                            ]);
                        }

                        Notification::make()->success()->title(__('Refund processed'))->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
