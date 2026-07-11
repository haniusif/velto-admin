<?php

namespace App\Filament\Resources\Zones\Schemas;

use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ZoneInfolist
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
                        TextEntry::make('color')->label(__('Color'))->badge()->color('gray'),
                        TextEntry::make('area.name')->label(__('Area'))->badge()->color('primary'),
                        TextEntry::make('area.city.name')->label(__('City'))->badge()->color('gray'),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                    ]),

                Section::make(__('Boundary'))
                    ->components([
                        MapEntry::make('geometry')
                            ->label('')
                            ->columnSpanFull()
                            ->extraStyles(['min-height: 480px', 'border-radius: 16px'])
                            ->defaultLocation(24.7136, 46.6753)
                            ->zoom(11)
                            ->showMarker(false)
                            ->geoMan(true)
                            ->geoManEditable(false)
                            ->setColor('#8863E5')
                            ->setFilledColor('#8863E5')
                            ->getStateUsing(function ($record): ?array {
                                if (! $record?->geometry) {
                                    return null;
                                }

                                return [
                                    'lat' => 24.7136,
                                    'lng' => 46.6753,
                                    'geojson' => $record->geometry,
                                ];
                            }),
                    ]),
            ]);
    }
}
