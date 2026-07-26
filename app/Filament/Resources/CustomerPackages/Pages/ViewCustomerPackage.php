<?php

namespace App\Filament\Resources\CustomerPackages\Pages;

use App\Filament\Resources\CustomerPackages\CustomerPackageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerPackage extends ViewRecord
{
    protected static string $resource = CustomerPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
