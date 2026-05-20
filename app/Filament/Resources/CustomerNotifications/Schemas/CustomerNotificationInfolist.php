<?php

namespace App\Filament\Resources\CustomerNotifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerNotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Header'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('customer.name')->label(__('Customer'))->badge()->color('primary'),
                        TextEntry::make('kind')->label(__('Kind'))->badge(),
                        IconEntry::make('read_at')
                            ->label(__('Read'))
                            ->boolean()
                            ->getStateUsing(fn ($record): bool => $record->read_at !== null),
                        TextEntry::make('title')->label(__('Title'))->columnSpanFull(),
                        TextEntry::make('title_ar')->label(__('Title (AR)'))->columnSpanFull()->placeholder('—'),
                    ]),

                Section::make(__('Body'))
                    ->components([
                        TextEntry::make('body')->label(__('English'))->placeholder('—')->columnSpanFull(),
                        TextEntry::make('body_ar')->label(__('Arabic'))->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make(__('Timeline'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('created_at')->label(__('Sent'))->dateTime('Y-m-d H:i'),
                        TextEntry::make('read_at')->label(__('Read at'))->dateTime('Y-m-d H:i')->placeholder('—'),
                    ]),
            ]);
    }
}
