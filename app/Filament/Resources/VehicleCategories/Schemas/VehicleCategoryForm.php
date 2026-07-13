<?php

namespace App\Filament\Resources\VehicleCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VehicleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('Code'))
                    ->required()
                    ->maxLength(16)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('code', Str::upper((string) $state)))
                    ->helperText(__('Short code such as A, B, C.')),
                TextInput::make('name')
                    ->label(__('Name (English)'))
                    ->required()
                    ->helperText(__('e.g. Large, Medium, Small.')),
                TextInput::make('name_ar')->label(__('Name (Arabic)')),
                TextInput::make('description')
                    ->label(__('Description (English)'))
                    ->helperText(__('e.g. SUV, pickup, van.')),
                TextInput::make('description_ar')->label(__('Description (Arabic)')),
                TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),
            ])
            ->columns(2);
    }
}
