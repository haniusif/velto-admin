<?php

namespace App\Filament\Resources\VehicleColors\Pages;

use App\Filament\Resources\VehicleColors\VehicleColorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleColor extends EditRecord
{
    protected static string $resource = VehicleColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
