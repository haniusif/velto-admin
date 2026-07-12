<?php

namespace App\Filament\Resources\Cities\Schemas;

use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name (English)'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),

                        TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('country')
                            ->label(__('Country (ISO-2)'))
                            ->default('SA')
                            ->maxLength(2),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make(__('Center on map'))
                    ->description(__('Click or drag to set the city center.'))
                    ->components([
                        Map::make('location')
                            ->label('')
                            ->columnSpanFull()
                            ->defaultLocation(24.7136, 46.6753) // Riyadh
                            ->zoom(11)
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
