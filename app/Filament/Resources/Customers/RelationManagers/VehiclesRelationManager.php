<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Vehicles');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label(__('Nickname'))->maxLength(255),
                TextInput::make('plate')->label(__('Plate'))->required()->maxLength(32),
                TextInput::make('brand')->label(__('Brand'))->required()->maxLength(255),
                TextInput::make('model')->label(__('Model'))->required()->maxLength(255),
                TextInput::make('color')->label(__('Color'))->maxLength(255),
                Toggle::make('is_default')->label(__('Default'))->inline(false),
                FileUpload::make('photo_url')
                    ->label(__('Photo'))
                    ->image()
                    ->disk('public')
                    ->directory('vehicles')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plate')
            ->columns([
                ImageColumn::make('photo_url')->label('')->disk('public')->height(40)
                    ->extraImgAttributes(['style' => 'border-radius: 8px;']),
                TextColumn::make('plate')->label(__('Plate'))->searchable()->copyable(),
                TextColumn::make('brand')->label(__('Brand')),
                TextColumn::make('model')->label(__('Model')),
                TextColumn::make('color')->label(__('Color'))->placeholder('—')->toggleable(),
                IconColumn::make('is_default')->label(__('Default'))->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label(__('Add vehicle')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('is_default', 'desc');
    }
}
