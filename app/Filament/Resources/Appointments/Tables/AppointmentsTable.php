<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class AppointmentsTable
{
    private const STATUS_COLORS = [
        'pending' => 'gray',
        'confirmed' => 'info',
        'on_the_way' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_name')
                    ->label(__('Service'))
                    ->description(fn (Appointment $r): ?string => $r->vehicle_label)
                    ->searchable(),

                TextColumn::make('scheduled_at')
                    ->label(__('Scheduled'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => __(ucwords(str_replace('_', ' ', $state)))),

                TextColumn::make('payment_method')
                    ->label(__('Payment'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'primary',
                        'card' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label(__('Paid'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'refunded' => 'warning',
                        'refund_pending' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('total_price')
                    ->label(__('Total'))
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Booked'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'confirmed' => __('Confirmed'),
                        'on_the_way' => __('On the way'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
                SelectFilter::make('payment_method')
                    ->label(__('Payment method'))
                    ->options([
                        'wallet' => __('Wallet'),
                        'card' => __('Card'),
                        'apple_pay' => __('Apple Pay'),
                    ]),
                SelectFilter::make('payment_status')
                    ->label(__('Payment status'))
                    ->options([
                        'pending' => __('Pending'),
                        'paid' => __('Paid'),
                        'refunded' => __('Refunded'),
                        'refund_pending' => __('Refund pending'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                self::cancelAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    /** Cancel an order: free its time slot and refund a wallet-paid booking. */
    private static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label(__('Cancel order'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Appointment $record): bool => in_array($record->status, Appointment::ACTIVE_STATUSES, true))
            ->action(function (Appointment $record): void {
                DB::transaction(function () use ($record): void {
                    $record->update([
                        'status' => Appointment::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                    ]);

                    if ($record->time_slot_id) {
                        $slot = TimeSlot::lockForUpdate()->find($record->time_slot_id);
                        if ($slot && $slot->booked_count > 0) {
                            $slot->decrement('booked_count');
                        }
                    }

                    if ($record->payment_status === 'paid') {
                        if ($record->payment_method === 'wallet') {
                            $record->customer?->walletTransactions()->create([
                                'kind' => WalletTransaction::KIND_REFUND,
                                'amount' => (float) $record->total_price,
                                'note' => "Refund — order #{$record->id} cancelled by admin",
                            ]);
                            $record->update(['payment_status' => 'refunded']);
                        } else {
                            // Card refunds go through the gateway/portal — flag it.
                            $record->update(['payment_status' => 'refund_pending']);
                        }
                    }
                });

                Notification::make()
                    ->title(__('Order cancelled'))
                    ->success()
                    ->send();
            });
    }
}
