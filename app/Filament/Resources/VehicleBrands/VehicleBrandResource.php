<?php

namespace App\Filament\Resources\VehicleBrands;

use App\Filament\Resources\VehicleBrands\Pages\CreateVehicleBrand;
use App\Filament\Resources\VehicleBrands\Pages\EditVehicleBrand;
use App\Filament\Resources\VehicleBrands\Pages\ListVehicleBrands;
use App\Filament\Resources\VehicleBrands\Pages\ViewVehicleBrand;
use App\Filament\Resources\VehicleBrands\RelationManagers\ModelsRelationManager;
use App\Filament\Resources\VehicleBrands\Schemas\VehicleBrandForm;
use App\Filament\Resources\VehicleBrands\Schemas\VehicleBrandInfolist;
use App\Filament\Resources\VehicleBrands\Tables\VehicleBrandsTable;
use App\Models\VehicleBrand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleBrandResource extends Resource
{
    protected static ?string $model = VehicleBrand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string { return __('Lookups'); }
    public static function getNavigationLabel(): string { return __('Vehicle brands'); }
    public static function getModelLabel(): string { return __('Vehicle brand'); }
    public static function getPluralModelLabel(): string { return __('Vehicle brands'); }

    public static function form(Schema $schema): Schema { return VehicleBrandForm::configure($schema); }
    public static function infolist(Schema $schema): Schema { return VehicleBrandInfolist::configure($schema); }
    public static function table(Table $table): Table { return VehicleBrandsTable::configure($table); }

    public static function getRelations(): array
    {
        return [ModelsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleBrands::route('/'),
            'create' => CreateVehicleBrand::route('/create'),
            'view' => ViewVehicleBrand::route('/{record}'),
            'edit' => EditVehicleBrand::route('/{record}/edit'),
        ];
    }
}
