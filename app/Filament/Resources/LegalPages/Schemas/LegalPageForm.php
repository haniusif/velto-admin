<?php

namespace App\Filament\Resources\LegalPages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LegalPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')->label(__('Slug'))->required()
                    ->placeholder('terms / privacy / refund')
                    ->unique(ignoreRecord: true),
                TextInput::make('version')->label(__('Version'))->default('1.0'),
                TextInput::make('title')->label(__('Title (English)'))->required(),
                TextInput::make('title_ar')->label(__('Title (Arabic)')),
                Textarea::make('body')->label(__('Body (English)'))->rows(12)->columnSpanFull(),
                Textarea::make('body_ar')->label(__('Body (Arabic)'))->rows(12)->columnSpanFull(),
                Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),
            ])
            ->columns(2);
    }
}
