<?php

namespace App\Filament\Resources\Areas\Tables;

use App\Models\City;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name_ar),

                TextColumn::make('city.name')
                    ->label(__('City'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('zones_count')
                    ->label(__('Zones'))
                    ->counts('zones')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('city_id')
                    ->label(__('City'))
                    ->options(fn () => City::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
