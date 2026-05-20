<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Vehicle'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('customer.name')->label(__('Customer'))->badge()->color('primary'),
                        TextEntry::make('plate')->label(__('Plate'))->copyable(),
                        TextEntry::make('name')->label(__('Nickname'))->placeholder('—'),
                        TextEntry::make('brand')->label(__('Brand')),
                        TextEntry::make('model')->label(__('Model')),
                        TextEntry::make('color')->label(__('Color'))->placeholder('—'),
                        IconEntry::make('is_default')->label(__('Default'))->boolean(),
                    ]),
                Section::make(__('Photo'))
                    ->components([
                        ImageEntry::make('photo_url')
                            ->label('')
                            ->disk('public')
                            ->height(220)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
