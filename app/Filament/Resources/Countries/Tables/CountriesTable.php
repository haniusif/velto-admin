<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('flag')->label(''),
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable()
                    ->description(fn ($record) => $record->name_ar),
                TextColumn::make('code')->label(__('Code'))->color('gray'),
                TextColumn::make('dial')->label(__('Dial')),
                TextColumn::make('phone_length')->label(__('Phone length'))->toggleable(),
                IconColumn::make('is_default')->label(__('Default'))->boolean()->toggleable(),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label(__('Active'))])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
