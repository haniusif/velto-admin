<?php

namespace App\Filament\Resources\WashPackages;

use App\Filament\Resources\WashPackages\Pages\CreateWashPackage;
use App\Filament\Resources\WashPackages\Pages\EditWashPackage;
use App\Filament\Resources\WashPackages\Pages\ListWashPackages;
use App\Filament\Resources\WashPackages\Pages\ViewWashPackage;
use App\Filament\Resources\WashPackages\RelationManagers\AddOnsRelationManager;
use App\Filament\Resources\WashPackages\Schemas\WashPackageForm;
use App\Filament\Resources\WashPackages\Schemas\WashPackageInfolist;
use App\Filament\Resources\WashPackages\Tables\WashPackagesTable;
use App\Models\WashPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WashPackageResource extends Resource
{
    protected static ?string $model = WashPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('Wash packages');
    }

    public static function getModelLabel(): string
    {
        return __('Wash package');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Wash packages');
    }

    public static function form(Schema $schema): Schema
    {
        return WashPackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WashPackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WashPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AddOnsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWashPackages::route('/'),
            'create' => CreateWashPackage::route('/create'),
            'view' => ViewWashPackage::route('/{record}'),
            'edit' => EditWashPackage::route('/{record}/edit'),
        ];
    }
}
