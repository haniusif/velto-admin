<?php

namespace App\Filament\Resources\VehicleBrands\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleBrandInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('Name')),
                TextEntry::make('name_ar')->label(__('Arabic name'))->placeholder('—'),
                TextEntry::make('slug')->label(__('Slug')),
                TextEntry::make('icon_path')->label(__('Icon'))->placeholder('—'),
                TextEntry::make('sort_order')->label(__('Sort order')),
                IconEntry::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->columns(2);
    }
}
