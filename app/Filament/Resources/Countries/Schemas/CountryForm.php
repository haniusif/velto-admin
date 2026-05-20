<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->label(__('ISO-2 code'))->required()->maxLength(2)->unique(ignoreRecord: true),
                TextInput::make('dial')->label(__('Dial code'))->required()->placeholder('+966'),
                TextInput::make('flag')->label(__('Flag emoji'))->maxLength(8),
                TextInput::make('name')->label(__('Name (English)'))->required(),
                TextInput::make('name_ar')->label(__('Name (Arabic)')),
                TextInput::make('phone_length')->label(__('Phone length'))->numeric()->default(9)->required(),
                TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
                Toggle::make('is_default')->label(__('Default'))->inline(false),
                Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),
            ])
            ->columns(2);
    }
}
