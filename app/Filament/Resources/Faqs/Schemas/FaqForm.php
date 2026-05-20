<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')->label(__('Question (English)'))->required()->columnSpanFull(),
                TextInput::make('question_ar')->label(__('Question (Arabic)'))->columnSpanFull(),
                Textarea::make('answer')->label(__('Answer (English)'))->rows(4)->columnSpanFull(),
                Textarea::make('answer_ar')->label(__('Answer (Arabic)'))->rows(4)->columnSpanFull(),
                TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),
            ])
            ->columns(2);
    }
}
