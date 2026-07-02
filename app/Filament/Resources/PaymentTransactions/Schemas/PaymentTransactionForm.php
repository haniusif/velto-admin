<?php

namespace App\Filament\Resources\PaymentTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('appointment_id')
                    ->relationship('appointment', 'id'),
                TextInput::make('gateway')
                    ->required()
                    ->default('arb'),
                TextInput::make('action')
                    ->required()
                    ->default('purchase'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('SAR'),
                TextInput::make('track_id')
                    ->required(),
                TextInput::make('payment_id'),
                TextInput::make('trans_id'),
                TextInput::make('ref'),
                TextInput::make('result_code'),
                TextInput::make('error_code'),
                TextInput::make('error_text'),
                TextInput::make('response_payload'),
            ]);
    }
}
