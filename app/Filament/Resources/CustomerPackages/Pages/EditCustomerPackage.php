<?php

namespace App\Filament\Resources\CustomerPackages\Pages;

use App\Filament\Resources\CustomerPackages\CustomerPackageResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPackage extends EditRecord
{
    protected static string $resource = CustomerPackageResource::class;

    protected function getHeaderActions(): array
    {
        // No delete: a plan is the record of a payment.
        return [
            ViewAction::make(),
        ];
    }
}
