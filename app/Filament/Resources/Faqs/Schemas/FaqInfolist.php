<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FaqInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('question')->label(__('Question'))->columnSpanFull(),
                TextEntry::make('question_ar')->label(__('Question (AR)'))->placeholder('—')->columnSpanFull(),
                TextEntry::make('answer')->label(__('Answer'))->placeholder('—')->columnSpanFull(),
                TextEntry::make('answer_ar')->label(__('Answer (AR)'))->placeholder('—')->columnSpanFull(),
                TextEntry::make('sort_order')->label(__('Sort order')),
                IconEntry::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->columns(2);
    }
}
