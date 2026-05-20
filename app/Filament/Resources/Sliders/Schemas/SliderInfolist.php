<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->components([
                        ImageEntry::make('image_path')
                            ->label(__('Image'))
                            ->disk('public')
                            ->height(280)
                            ->columnSpanFull(),

                        TextEntry::make('sort_order')->label(__('Sort order')),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                    ])
                    ->columns(2),
            ]);
    }
}
