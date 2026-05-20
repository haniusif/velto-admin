<?php

namespace App\Filament\Resources\Workers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('city')
                    ->label(__('City'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),

                TextColumn::make('rating')
                    ->label(__('Rating'))
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' / 5')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('hire_date')
                    ->label(__('Hire date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active' => __('Active'),
                        'inactive' => __('Inactive'),
                        'suspended' => __('Suspended'),
                    ]),

                SelectFilter::make('city')
                    ->label(__('City'))
                    ->options(fn () => \App\Models\Worker::query()
                        ->select('city')
                        ->distinct()
                        ->pluck('city', 'city')
                        ->all()),
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
            ->defaultSort('created_at', 'desc');
    }
}
