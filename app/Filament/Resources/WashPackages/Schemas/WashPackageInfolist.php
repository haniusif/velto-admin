<?php

namespace App\Filament\Resources\WashPackages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WashPackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('name_ar')->label(__('Arabic name'))->placeholder('—'),
                        TextEntry::make('type')
                            ->label(__('Type'))
                            ->badge()
                            ->color(fn (string $state): string => $state === 'multi' ? 'warning' : 'primary')
                            ->formatStateUsing(fn (string $state): string => $state === 'multi' ? __('Multi-visit') : __('Single')),
                        TextEntry::make('price')->label(__('Price'))->money('SAR'),
                        TextEntry::make('duration_minutes')->label(__('Duration'))->suffix(' '.__('min')),
                        TextEntry::make('visits_count')->label(__('Visits'))->placeholder('—'),
                        TextEntry::make('validity_days')->label(__('Validity'))->suffix(' '.__('days'))->placeholder('—'),
                    ]),

                Section::make(__('Description'))
                    ->components([
                        TextEntry::make('description')->label(__('English'))->placeholder('—')->columnSpanFull(),
                        TextEntry::make('description_ar')->label(__('Arabic'))->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make(__('Image'))
                    ->components([
                        ImageEntry::make('image_path')
                            ->label('')
                            ->disk('public')
                            ->height(220)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Settings'))
                    ->columns(3)
                    ->components([
                        IconEntry::make('is_featured')->label(__('Featured'))->boolean(),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                        TextEntry::make('sort_order')->label(__('Sort order')),
                    ]),
            ]);
    }
}
