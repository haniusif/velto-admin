<?php

namespace App\Filament\Resources\LegalPages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LegalPageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('slug')->label(__('Slug'))->badge()->color('primary'),
                TextEntry::make('version')->label(__('Version')),
                TextEntry::make('title')->label(__('Title'))->columnSpanFull(),
                TextEntry::make('title_ar')->label(__('Title (AR)'))->placeholder('—')->columnSpanFull(),
                TextEntry::make('body')->label(__('Body'))->placeholder('—')->columnSpanFull(),
                TextEntry::make('body_ar')->label(__('Body (AR)'))->placeholder('—')->columnSpanFull(),
                IconEntry::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->columns(2);
    }
}
