<?php

namespace App\Filament\Resources\WashPackages\Pages;

use App\Filament\Resources\WashPackages\WashPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWashPackages extends ListRecords
{
    protected static string $resource = WashPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
