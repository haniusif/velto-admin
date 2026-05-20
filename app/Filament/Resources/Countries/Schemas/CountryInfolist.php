<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CountryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('flag')->label(__('Flag')),
                TextEntry::make('code')->label(__('Code')),
                TextEntry::make('dial')->label(__('Dial')),
                TextEntry::make('name')->label(__('Name')),
                TextEntry::make('name_ar')->label(__('Arabic name'))->placeholder('—'),
                TextEntry::make('phone_length')->label(__('Phone length')),
                IconEntry::make('is_default')->label(__('Default'))->boolean(),
                IconEntry::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->columns(2);
    }
}
