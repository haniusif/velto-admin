<?php

namespace App\Filament\Resources\CustomerPackages;

use App\Filament\Resources\CustomerPackages\Pages\EditCustomerPackage;
use App\Filament\Resources\CustomerPackages\Pages\ListCustomerPackages;
use App\Filament\Resources\CustomerPackages\Pages\ViewCustomerPackage;
use App\Filament\Resources\CustomerPackages\Schemas\CustomerPackageForm;
use App\Filament\Resources\CustomerPackages\Schemas\CustomerPackageInfolist;
use App\Filament\Resources\CustomerPackages\Tables\CustomerPackagesTable;
use App\Models\CustomerPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Prepaid multi-visit plans customers have bought.
 *
 * Deliberately view/edit only: a plan is the record of a payment, so it is
 * neither created nor deleted from the panel. Ops adjust the levers that
 * matter — visits, expiry, status — and the purchase itself stays intact.
 */
class CustomerPackageResource extends Resource
{
    protected static ?string $model = CustomerPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('Plans');
    }

    public static function getModelLabel(): string
    {
        return __('Plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Plans');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerPackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerPackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPackages::route('/'),
            'view' => ViewCustomerPackage::route('/{record}'),
            'edit' => EditCustomerPackage::route('/{record}/edit'),
        ];
    }
}
