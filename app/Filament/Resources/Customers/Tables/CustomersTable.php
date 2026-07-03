<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('city')
                    ->label(__('City'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('preferred_language')
                    ->label(__('Lang'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),

                TextColumn::make('joined_at')
                    ->label(__('Joined'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active' => __('Active'),
                        'inactive' => __('Inactive'),
                        'blocked' => __('Blocked'),
                    ]),

                SelectFilter::make('preferred_language')
                    ->label(__('Language'))
                    ->options([
                        'ar' => __('Arabic'),
                        'en' => __('English'),
                    ]),

                SelectFilter::make('city')
                    ->label(__('City'))
                    ->options(fn () => \App\Models\Customer::query()
                        ->select('city')
                        ->distinct()
                        ->pluck('city', 'city')
                        ->all()),
            ])
            ->recordActions([
                Action::make('topUp')
                    ->label(__('Top-up'))
                    ->icon('heroicon-o-wallet')
                    ->color('success')
                    ->modalHeading(__('Top-up'))
                    ->schema([
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->suffix('SAR'),
                        TextInput::make('note')
                            ->label(__('Note'))
                            ->maxLength(255),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        $record->walletTransactions()->create([
                            'kind' => WalletTransaction::KIND_TOP_UP,
                            'amount' => (float) $data['amount'],
                            'note' => $data['note'] ?? 'Manual top-up (admin)',
                        ]);
                        Notification::make()->success()
                            ->title(__('Top-up'))
                            ->body(number_format((float) $data['amount'], 2).' SAR')
                            ->send();
                    }),
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
