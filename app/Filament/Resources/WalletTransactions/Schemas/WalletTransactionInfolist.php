<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->components([
                        TextEntry::make('customer.name')->label(__('Customer'))->badge()->color('primary'),
                        TextEntry::make('kind')->label(__('Kind'))->badge(),
                        TextEntry::make('amount')->label(__('Amount'))->money('SAR'),
                        TextEntry::make('created_at')->label(__('When'))->dateTime('Y-m-d H:i'),
                        TextEntry::make('note')->label(__('Note'))->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
