<?php

namespace App\Filament\Resources\LegalPages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LegalPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')->label(__('Slug'))->badge()->color('primary'),
                TextColumn::make('title')->label(__('Title'))->searchable()
                    ->description(fn ($record) => $record->title_ar),
                TextColumn::make('version')->label(__('Version'))->color('gray'),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
                TextColumn::make('updated_at')->label(__('Updated'))->dateTime('Y-m-d H:i')->toggleable(),
            ])
            ->filters([TernaryFilter::make('is_active')->label(__('Active'))])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('slug');
    }
}
