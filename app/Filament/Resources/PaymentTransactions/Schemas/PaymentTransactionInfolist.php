<?php

namespace App\Filament\Resources\PaymentTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label(__('Customer')),
                TextEntry::make('appointment.id')
                    ->label(__('Appointment'))
                    ->placeholder('-'),
                TextEntry::make('gateway')
                    ->label(__('Gateway')),
                TextEntry::make('action')
                    ->label(__('Action')),
                TextEntry::make('status')
                    ->label(__('Status')),
                TextEntry::make('amount')
                    ->label(__('Amount'))
                    ->numeric(),
                TextEntry::make('currency')
                    ->label(__('Currency')),
                TextEntry::make('track_id')
                    ->label(__('Track ID')),
                TextEntry::make('payment_id')
                    ->label(__('Payment ID'))
                    ->placeholder('-'),
                TextEntry::make('trans_id')
                    ->label(__('Transaction ID'))
                    ->placeholder('-'),
                TextEntry::make('ref')
                    ->label(__('Ref (RRN)'))
                    ->placeholder('-'),
                TextEntry::make('result_code')
                    ->label(__('Result code'))
                    ->placeholder('-'),
                TextEntry::make('error_code')
                    ->label(__('Error code'))
                    ->placeholder('-'),
                TextEntry::make('error_text')
                    ->label(__('Error text'))
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
