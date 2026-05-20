<?php

namespace App\Filament\Resources\Areas\Schemas;

use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AreaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('name_ar')->label(__('Arabic name'))->placeholder('—'),
                        TextEntry::make('city.name')->label(__('City'))->badge()->color('primary'),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                        TextEntry::make('created_at')->label(__('Created'))->dateTime('Y-m-d H:i'),
                    ]),

                Section::make(__('Center'))
                    ->components([
                        MapEntry::make('location')
                            ->label('')
                            ->columnSpanFull()
                            ->extraStyles(['min-height: 360px', 'border-radius: 16px'])
                            ->defaultLocation([24.7136, 46.6753])
                            ->zoom(13)
                            ->showMarker(true)
                            ->markerColor('#8863E5')
                            ->getStateUsing(fn ($record) => $record?->latitude && $record?->longitude
                                ? ['lat' => (float) $record->latitude, 'lng' => (float) $record->longitude]
                                : null),
                    ]),
            ]);
    }
}
