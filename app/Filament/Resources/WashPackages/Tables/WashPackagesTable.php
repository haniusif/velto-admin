<?php

namespace App\Filament\Resources\WashPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WashPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->height(48)
                    ->extraImgAttributes(['style' => 'border-radius: 8px;']),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name_ar),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'multi' ? 'warning' : 'primary')
                    ->formatStateUsing(fn (string $state): string => $state === 'multi' ? __('Multi-visit') : __('Single')),

                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('SAR', 0)
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label(__('Duration'))
                    ->suffix(' '.__('min'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('visits_count')
                    ->label(__('Visits'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('add_ons_count')
                    ->label(__('Add-ons'))
                    ->counts('addOns')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'single' => __('Single visit'),
                        'multi' => __('Multi-visit plan'),
                    ]),

                TernaryFilter::make('is_active')
                    ->label(__('Active')),

                TernaryFilter::make('is_featured')
                    ->label(__('Featured')),
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
            ->defaultSort('sort_order');
    }
}
