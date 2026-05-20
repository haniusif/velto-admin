<?php

namespace App\Filament\Resources\WashPackages\RelationManagers;

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

class AddOnsRelationManager extends RelationManager
{
    protected static string $relationship = 'addOns';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Add-ons');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name (English)'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->placeholder(__('e.g. فواحة، معطر'))
                    ->maxLength(255),

                TextInput::make('extra_price')
                    ->label(__('Extra price'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->suffix('SAR')
                    ->required()
                    ->default(0),

                FileUpload::make('icon')
                    ->label(__('Icon'))
                    ->image()
                    ->disk('public')
                    ->directory('add-ons')
                    ->imagePreviewHeight('80')
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label(__('Sort order'))
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true)
                    ->inline(false),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('icon')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->extraImgAttributes(['style' => 'border-radius: 8px;']),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->description(fn ($record) => $record->name_ar),

                TextColumn::make('extra_price')
                    ->label(__('Extra price'))
                    ->money('SAR'),

                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label(__('Add add-on')),
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
            ->defaultSort('sort_order');
    }
}
