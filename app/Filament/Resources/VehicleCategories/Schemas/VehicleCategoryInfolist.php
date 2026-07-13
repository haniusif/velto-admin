<?php

namespace App\Filament\Resources\VehicleCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')->label(__('Code')),
                TextEntry::make('name')->label(__('Name')),
                TextEntry::make('name_ar')->label(__('Arabic name'))->placeholder('—'),
                TextEntry::make('description')->label(__('Description'))->placeholder('—'),
                TextEntry::make('description_ar')->label(__('Arabic description'))->placeholder('—'),
                TextEntry::make('sort_order')->label(__('Sort order')),
                IconEntry::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->columns(2);
    }
}
