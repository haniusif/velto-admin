<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('phone')->label(__('Phone'))->copyable(),
                        TextEntry::make('email')->label(__('Email'))->placeholder('—')->copyable(),
                        TextEntry::make('city')->label(__('City')),
                        TextEntry::make('preferred_language')
                            ->label(__('Preferred language'))
                            ->formatStateUsing(fn (string $state): string => strtoupper($state))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'blocked' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                    ]),

                Section::make(__('Activity'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('joined_at')->label(__('Joined at'))->dateTime('Y-m-d H:i')->placeholder('—'),
                        TextEntry::make('created_at')->label(__('Created'))->dateTime('Y-m-d H:i'),
                    ]),

                Section::make(__('Notes'))
                    ->components([
                        TextEntry::make('notes')->label(__('Notes'))->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
