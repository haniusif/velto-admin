<?php

namespace App\Filament\Resources\WashPackages\Pages;

use App\Filament\Resources\WashPackages\WashPackageResource;
use App\Models\WashPackage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWashPackages extends ListRecords
{
    protected static string $resource = WashPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Quick tabs above the list to switch between all, active and inactive
     * packages, each badged with its count.
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All packages'))
                ->badge(WashPackage::count()),

            'active' => Tab::make(__('Active packages'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(WashPackage::where('is_active', true)->count()),

            'inactive' => Tab::make(__('Inactive packages'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(WashPackage::where('is_active', false)->count()),
        ];
    }
}
