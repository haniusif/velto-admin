<?php

namespace App\Filament\Resources\Zones;

use App\Filament\Resources\Zones\Pages\CreateZone;
use App\Filament\Resources\Zones\Pages\EditZone;
use App\Filament\Resources\Zones\Pages\ListZones;
use App\Filament\Resources\Zones\Pages\ViewZone;
use App\Filament\Resources\Zones\Schemas\ZoneForm;
use App\Filament\Resources\Zones\Schemas\ZoneInfolist;
use App\Filament\Resources\Zones\Tables\ZonesTable;
use App\Models\Zone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return __('Locations');
    }

    public static function getNavigationLabel(): string
    {
        return __('Zones');
    }

    public static function getModelLabel(): string
    {
        return __('Zone');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Zones');
    }

    public static function form(Schema $schema): Schema
    {
        return ZoneForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ZoneInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ZonesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZones::route('/'),
            'create' => CreateZone::route('/create'),
            'view' => ViewZone::route('/{record}'),
            'edit' => EditZone::route('/{record}/edit'),
        ];
    }
}
