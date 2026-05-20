<?php

namespace App\Filament\Resources\VehicleBrands\Pages;

use App\Filament\Resources\VehicleBrands\VehicleBrandResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleBrand extends ViewRecord
{
    protected static string $resource = VehicleBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
