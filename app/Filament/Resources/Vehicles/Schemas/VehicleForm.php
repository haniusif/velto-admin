<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Owner'))
                    ->components([
                        Select::make('customer_id')
                            ->label(__('Customer'))
                            ->options(fn () => Customer::query()
                                ->orderBy('name')
                                ->limit(200)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make(__('Vehicle'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Nickname'))
                            ->maxLength(255),

                        TextInput::make('plate')
                            ->label(__('Plate'))
                            ->required()
                            ->maxLength(32),

                        TextInput::make('brand')
                            ->label(__('Brand'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('model')
                            ->label(__('Model'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('color')
                            ->label(__('Color'))
                            ->maxLength(255),

                        Toggle::make('is_default')
                            ->label(__('Default vehicle'))
                            ->inline(false),
                    ]),

                Section::make(__('Photo'))
                    ->components([
                        FileUpload::make('photo_url')
                            ->label(__('Photo'))
                            ->image()
                            ->disk('public')
                            ->directory('vehicles')
                            ->imagePreviewHeight('180')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
