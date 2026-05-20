<?php

namespace App\Filament\Resources\AppSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                \Filament\Tables\Grouping\Group::make('group')->label(__('Group')),
            ])
            ->defaultGroup('group')
            ->columns([
                TextColumn::make('group')->label(__('Group'))->badge()->color('primary'),
                TextColumn::make('key')->label(__('Key'))->searchable()->copyable()
                    ->description(fn ($record) => $record->label),
                TextColumn::make('value')->label(__('Value'))->limit(60)->wrap()->searchable(),
                TextColumn::make('type')->label(__('Type'))->badge()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('group')->options(fn () => \App\Models\AppSetting::query()
                    ->select('group')
                    ->distinct()
                    ->pluck('group', 'group')
                    ->all()),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('key');
    }
}
