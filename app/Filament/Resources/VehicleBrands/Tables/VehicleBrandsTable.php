<?php

namespace App\Filament\Resources\VehicleBrands\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VehicleBrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable()
                    ->description(fn ($record) => $record->name_ar),
                TextColumn::make('slug')->label(__('Slug'))->color('gray')->toggleable(),
                TextColumn::make('models_count')->label(__('Models'))->counts('models')->badge()->color('primary'),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label(__('Active'))])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
