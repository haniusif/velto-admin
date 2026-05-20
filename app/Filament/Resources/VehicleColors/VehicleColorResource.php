<?php

namespace App\Filament\Resources\VehicleColors;

use App\Filament\Resources\VehicleColors\Pages\CreateVehicleColor;
use App\Filament\Resources\VehicleColors\Pages\EditVehicleColor;
use App\Filament\Resources\VehicleColors\Pages\ListVehicleColors;
use App\Filament\Resources\VehicleColors\Pages\ViewVehicleColor;
use App\Filament\Resources\VehicleColors\Schemas\VehicleColorForm;
use App\Filament\Resources\VehicleColors\Schemas\VehicleColorInfolist;
use App\Filament\Resources\VehicleColors\Tables\VehicleColorsTable;
use App\Models\VehicleColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleColorResource extends Resource
{
    protected static ?string $model = VehicleColor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): ?string { return __('Lookups'); }
    public static function getNavigationLabel(): string { return __('Vehicle colors'); }
    public static function getModelLabel(): string { return __('Vehicle color'); }
    public static function getPluralModelLabel(): string { return __('Vehicle colors'); }

    public static function form(Schema $schema): Schema { return VehicleColorForm::configure($schema); }
    public static function infolist(Schema $schema): Schema { return VehicleColorInfolist::configure($schema); }
    public static function table(Table $table): Table { return VehicleColorsTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleColors::route('/'),
            'create' => CreateVehicleColor::route('/create'),
            'view' => ViewVehicleColor::route('/{record}'),
            'edit' => EditVehicleColor::route('/{record}/edit'),
        ];
    }
}
