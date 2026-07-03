<?php

namespace App\Filament\Resources\VehicleBrands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VehicleBrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name (English)'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state, '_'))),
                TextInput::make('name_ar')->label(__('Name (Arabic)')),
                TextInput::make('slug')->label(__('Slug'))->required()->unique(ignoreRecord: true),
                FileUpload::make('icon_path')
                    ->label(__('Icon'))
                    ->disk('public')
                    ->directory('brands')
                    ->visibility('public')
                    ->image()
                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'])
                    ->maxSize(1024)
                    ->imagePreviewHeight('56')
                    ->helperText(__('SVG or PNG logo shown in the app brand picker.')),
                TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),
            ])
            ->columns(2);
    }
}
