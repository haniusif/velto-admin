<?php

namespace App\Filament\Resources\WashPackages\Pages;

use App\Filament\Resources\WashPackages\WashPackageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWashPackage extends ViewRecord
{
    protected static string $resource = WashPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
