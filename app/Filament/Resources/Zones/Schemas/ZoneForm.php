<?php

namespace App\Filament\Resources\Zones\Schemas;

use App\Models\Area;
use App\Models\City;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ZoneForm
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
                            ->options(fn () => City::query()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, $record): void {
                                if ($record?->area) {
                                    $set('city_id', $record->area->city_id);
                                }
                            })
                            ->afterStateUpdated(fn (Set $set) => $set('area_id', null)),

                        Select::make('area_id')
                            ->label(__('Area'))
                            ->options(fn (Get $get) => $get('city_id')
                                ? Area::query()->where('city_id', $get('city_id'))->orderBy('name')->pluck('name', 'id')
                                : [])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get) => ! $get('city_id'))
                            ->live(),

                        TextInput::make('name')
                            ->label(__('Name (English)'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->maxLength(255),

                        ColorPicker::make('color')
                            ->label(__('Color'))
                            ->default('#8863E5')
                            ->required(),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make(__('Service boundary'))
                    ->description(__('Use the polygon tool on the map to draw the zone boundary. Click each corner; double-click the first point to close the shape.'))
                    ->components([
                        Map::make('geometry')
                            ->label('')
                            ->columnSpanFull()
                            ->defaultLocation([24.7136, 46.6753])
                            ->zoom(11)
                            ->draggable()
                            ->showZoomControl()
                            ->showFullscreenControl()
                            ->showMyLocationButton()
                            ->showMarker(false)
                            ->geoMan(true)
                            ->geoManEditable(true)
                            ->drawPolygon(true)
                            ->drawMarker(false)
                            ->drawCircleMarker(false)
                            ->drawPolyline(false)
                            ->drawCircle(false)
                            ->drawRectangle(false)
                            ->drawText(false)
                            ->editPolygon(true)
                            ->cutPolygon(true)
                            ->deleteLayer(true)
                            ->dragMode(true)
                            ->setColor('#8863E5')
                            ->setFilledColor('#8863E5')
                            ->snappable(true, 20)
                            ->extraStyles(['min-height: 560px', 'border-radius: 16px'])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if (isset($state['geojson'])) {
                                    $set('geometry', $state['geojson']);
                                }
                            })
                            ->afterStateHydrated(function ($state, $record, Set $set): void {
                                if ($record?->geometry) {
                                    $center = self::geojsonCenter($record->geometry);
                                    $set('geometry', [
                                        'lat' => $center[0] ?? 24.7136,
                                        'lng' => $center[1] ?? 46.6753,
                                        'geojson' => $record->geometry,
                                    ]);
                                }
                            }),
                    ]),
            ]);
    }

    private static function geojsonCenter(?array $geojson): array
    {
        if (! $geojson) {
            return [24.7136, 46.6753];
        }

        $features = $geojson['features'] ?? [$geojson];
        $latSum = 0.0;
        $lngSum = 0.0;
        $count = 0;

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? $feature;
            $type = $geometry['type'] ?? null;
            $coords = $geometry['coordinates'] ?? null;
            if (! $coords) {
                continue;
            }

            if ($type === 'Polygon') {
                foreach (($coords[0] ?? []) as [$lng, $lat]) {
                    $latSum += $lat;
                    $lngSum += $lng;
                    $count++;
                }
            } elseif ($type === 'MultiPolygon') {
                foreach ($coords as $polygon) {
                    foreach (($polygon[0] ?? []) as [$lng, $lat]) {
                        $latSum += $lat;
                        $lngSum += $lng;
                        $count++;
                    }
                }
            }
        }

        return $count > 0 ? [$latSum / $count, $lngSum / $count] : [24.7136, 46.6753];
    }
}
