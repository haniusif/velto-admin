<?php

namespace App\Filament\Resources\VehicleCategories;

use App\Filament\Resources\VehicleCategories\Pages\CreateVehicleCategory;
use App\Filament\Resources\VehicleCategories\Pages\EditVehicleCategory;
use App\Filament\Resources\VehicleCategories\Pages\ListVehicleCategories;
use App\Filament\Resources\VehicleCategories\Pages\ViewVehicleCategory;
use App\Filament\Resources\VehicleCategories\Schemas\VehicleCategoryForm;
use App\Filament\Resources\VehicleCategories\Schemas\VehicleCategoryInfolist;
use App\Filament\Resources\VehicleCategories\Tables\VehicleCategoriesTable;
use App\Models\VehicleCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleCategoryResource extends Resource
{
    protected static ?string $model = VehicleCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string { return __('Lookups'); }
    public static function getNavigationLabel(): string { return __('Vehicle categories'); }
    public static function getModelLabel(): string { return __('Vehicle category'); }
    public static function getPluralModelLabel(): string { return __('Vehicle categories'); }

    public static function form(Schema $schema): Schema { return VehicleCategoryForm::configure($schema); }
    public static function infolist(Schema $schema): Schema { return VehicleCategoryInfolist::configure($schema); }
    public static function table(Table $table): Table { return VehicleCategoriesTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleCategories::route('/'),
            'create' => CreateVehicleCategory::route('/create'),
            'view' => ViewVehicleCategory::route('/{record}'),
            'edit' => EditVehicleCategory::route('/{record}/edit'),
        ];
    }
}
