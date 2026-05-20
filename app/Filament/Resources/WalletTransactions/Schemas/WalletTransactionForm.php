<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        Select::make('customer_id')
                            ->label(__('Customer'))
                            ->options(fn () => Customer::query()
                                ->orderBy('name')
                                ->limit(500)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('kind')
                            ->label(__('Kind'))
                            ->options([
                                WalletTransaction::KIND_TOP_UP => __('Top-up'),
                                WalletTransaction::KIND_BOOKING => __('Booking'),
                                WalletTransaction::KIND_REFUND => __('Refund'),
                                WalletTransaction::KIND_ADJUSTMENT => __('Adjustment'),
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->helperText(__('Positive = credit, negative = debit'))
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->suffix('SAR'),

                        Textarea::make('note')
                            ->label(__('Note'))
                            ->rows(2)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
