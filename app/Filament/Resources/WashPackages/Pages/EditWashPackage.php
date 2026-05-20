<?php

namespace App\Filament\Resources\WashPackages\Pages;

use App\Filament\Resources\WashPackages\WashPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWashPackage extends EditRecord
{
    protected static string $resource = WashPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
