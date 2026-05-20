<?php

namespace App\Filament\Resources\Zones\Tables;

use App\Models\Area;
use App\Models\City;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label(''),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name_ar),

                TextColumn::make('area.name')
                    ->label(__('Area'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('area.city.name')
                    ->label(__('City'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                IconColumn::make('geometry')
                    ->label(__('Has shape'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->geometry)),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('area_id')
                    ->label(__('Area'))
                    ->options(fn () => Area::query()
                        ->with('city')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Area $a) => [$a->id => "{$a->city?->name} · {$a->name}"])
                        ->all())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('city_id')
                    ->label(__('City'))
                    ->options(fn () => City::query()->orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $cityId) => $q->whereHas('area', fn ($qq) => $qq->where('city_id', $cityId))
                    )),

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
