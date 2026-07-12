<?php

namespace App\Filament\Resources\Areas\Schemas;

use App\Models\City;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(2)
                    ->components([
                        Select::make('city_id')
                            ->label(__('City'))
                            ->relationship('city', 'name')
                            ->options(fn () => City::query()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?int $state): void {
                                $city = $state ? City::find($state) : null;
                                if ($city?->latitude && $city?->longitude) {
                                    $set('location', [
                                        'lat' => (float) $city->latitude,
                                        'lng' => (float) $city->longitude,
                                    ]);
                                }
                            }),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),

                        TextInput::make('name')
                            ->label(__('Name (English)'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->maxLength(255),
                    ]),

                Section::make(__('Center on map'))
                    ->description(__('Click or drag to set the area center.'))
                    ->components([
                        Map::make('location')
                            ->label('')
                            ->columnSpanFull()
                            ->defaultLocation(24.7136, 46.6753)
                            ->zoom(13)
                            ->draggable()
                            ->clickable(true)
                            ->showZoomControl()
                            ->showFullscreenControl()
                            ->showMyLocationButton()
                            ->markerColor('#8863E5')
                            ->extraStyles(['min-height: 480px', 'border-radius: 16px'])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if (! $state) {
                                    return;
                                }
                                $set('latitude', $state['lat'] ?? null);
                                $set('longitude', $state['lng'] ?? null);
                            })
                            ->afterStateHydrated(function ($state, $record, Set $set): void {
                                if ($record?->latitude && $record?->longitude) {
                                    $set('location', [
                                        'lat' => (float) $record->latitude,
                                        'lng' => (float) $record->longitude,
                                    ]);
                                }
                            })
                            ->dehydrated(false),

                        TextInput::make('latitude')
                            ->label(__('Latitude'))
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('longitude')
                            ->label(__('Longitude'))
                            ->numeric()
                            ->readOnly(),
                    ])
                    ->columns(2),
            ]);
    }
}
