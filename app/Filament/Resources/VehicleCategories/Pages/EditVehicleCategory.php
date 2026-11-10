<?php

namespace App\Filament\Resources\VehicleCategories\Pages;

use App\Filament\Resources\VehicleCategories\VehicleCategoryResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleCategory extends EditRecord
{
    protected static string $resource = VehicleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
