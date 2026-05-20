<?php

namespace App\Filament\Resources\VehicleColors\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleColorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('Name')),
                TextEntry::make('name_ar')->label(__('Arabic name'))->placeholder('—'),
                TextEntry::make('hex')->label(__('Hex'))->badge(),
                IconEntry::make('is_light_swatch')->label(__('Light swatch'))->boolean(),
                TextEntry::make('sort_order')->label(__('Sort order')),
                IconEntry::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->columns(2);
    }
}
