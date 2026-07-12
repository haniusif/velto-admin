<?php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Resources\Cities\CityResource;
use App\Models\City;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All'))
                ->badge(City::count()),

            'active' => Tab::make(__('Active'))
                ->badge(City::where('is_active', true)->count())
                ->badgeColor('success')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            'inactive' => Tab::make(__('Inactive'))
                ->badge(City::where('is_active', false)->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
