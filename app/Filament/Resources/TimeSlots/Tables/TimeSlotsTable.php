<?php

namespace App\Filament\Resources\TimeSlots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;

class TimeSlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                \Filament\Tables\Grouping\Group::make('date')
                    ->label(__('Date'))
                    ->date('Y-m-d (l)')
                    ->collapsible(),
            ])
            ->defaultGroup('date')
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label(__('Start'))
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label(__('End'))
                    ->time('H:i'),

                TextColumn::make('booked_count')
                    ->label(__('Booked'))
                    ->formatStateUsing(fn ($state, $record) => "{$state} / {$record->capacity}")
                    ->badge()
                    ->color(fn ($record) => $record->booked_count >= $record->capacity ? 'danger' : ($record->booked_count > 0 ? 'warning' : 'success')),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->label(__('Date range'))
                    ->schema([
                        DatePicker::make('from')->label(__('From'))->native(false),
                        DatePicker::make('to')->label(__('To'))->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('date', '>=', $d))
                            ->when($data['to'] ?? null, fn ($q, $d) => $q->whereDate('date', '<=', $d));
                    }),

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
            ->defaultSort('date', 'asc');
    }
}
