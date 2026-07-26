<?php

namespace App\Filament\Resources\CustomerPackages\Pages;

use App\Filament\Resources\CustomerPackages\CustomerPackageResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPackages extends ListRecords
{
    protected static string $resource = CustomerPackageResource::class;
}
