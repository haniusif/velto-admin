<?php

namespace App\Filament\Resources\VehicleBrands\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Models');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label(__('Name'))->required()->maxLength(255),
                TextInput::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->helperText(__('Optional — falls back to the Latin name.'))
                    ->maxLength(255),
                // Size band drives how much work a wash is — a Yaris and a
                // Land Cruiser are not the same job. Nullable on purpose: a new
                // model is unclassified rather than silently the smallest.
                Select::make('vehicle_category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' — '.$record->name_ar)
                    ->searchable()
                    ->preload()
                    ->placeholder(__('Unclassified')),
                TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable(),
                TextColumn::make('name_ar')->label(__('Name (Arabic)'))->placeholder('—')->searchable(),
                TextColumn::make('category.name_ar')
                    ->label(__('Category'))
                    ->placeholder(__('Unclassified'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'كبيرة' => 'warning',
                        'صغيرة' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('vehicle_category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->placeholder(__('All')),
            ])
            ->headerActions([CreateAction::make()->label(__('Add model'))])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
