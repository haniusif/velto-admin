<?php

namespace App\Filament\Resources\VehicleColors\Pages;

use App\Filament\Resources\VehicleColors\VehicleColorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleColor extends ViewRecord
{
    protected static string $resource = VehicleColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
