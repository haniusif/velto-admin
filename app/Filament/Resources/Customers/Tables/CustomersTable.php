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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->searchable()
                    ->options(fn () => \App\Models\Customer::query()
                        ->whereNotNull('city')
                        ->where('city', '!=', '')
                        ->distinct()
                        ->orderBy('city')
                        ->pluck('city', 'city')
                        ->all()),

                SelectFilter::make('area')
                    ->label(__('Area'))
                    ->searchable()
                    ->options(fn () => \App\Models\Customer::query()
                        ->whereNotNull('area')
                        ->where('area', '!=', '')
                        ->distinct()
                        ->orderBy('area')
                        ->pluck('area', 'area')
                        ->all()),

                SelectFilter::make('gender')
                    ->label(__('Gender'))
                    ->options([
                        'male' => __('Male'),
                        'female' => __('Female'),
                    ]),

                TernaryFilter::make('profile_completed')
                    ->label(__('Profile'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Completed'))
                    ->falseLabel(__('Incomplete')),

                TernaryFilter::make('wallet_balance')
                    ->label(__('Wallet'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Has balance'))
                    ->falseLabel(__('No balance'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('wallet_balance', '>', 0),
                        false: fn (Builder $query) => $query->where('wallet_balance', '<=', 0),
                        blank: fn (Builder $query) => $query,
                    ),

                Filter::make('joined_at')
                    ->label(__('Joined'))
                    ->schema([
                        DatePicker::make('joined_from')->label(__('Joined from')),
                        DatePicker::make('joined_until')->label(__('Joined until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['joined_from'] ?? null,
                            fn (Builder $q, $date) => $q->whereDate('joined_at', '>=', $date))
                        ->when($data['joined_until'] ?? null,
                            fn (Builder $q, $date) => $q->whereDate('joined_at', '<=', $date))),
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
