<?php

namespace App\Filament\Resources\WashPackages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WashPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name (English)'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label(__('Description (English)'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('description_ar')
                            ->label(__('Description (Arabic)'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Pricing & duration'))
                    ->columns(3)
                    ->components([
                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'single' => __('Single visit'),
                                'multi' => __('Multi-visit plan'),
                            ])
                            ->default('single')
                            ->required()
                            ->live()
                            ->native(false),

                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->suffix('SAR')
                            ->required(),

                        TextInput::make('duration_minutes')
                            ->label(__('Duration'))
                            ->numeric()
                            ->minValue(1)
                            ->suffix(__('min'))
                            ->required()
                            ->default(30),

                        TextInput::make('visits_count')
                            ->label(__('Visits'))
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->visible(fn (Get $get) => $get('type') === 'multi'),

                        TextInput::make('validity_days')
                            ->label(__('Validity'))
                            ->numeric()
                            ->minValue(1)
                            ->suffix(__('days'))
                            ->required()
                            ->visible(fn (Get $get) => $get('type') === 'multi')
                            ->default(30),
                    ]),

                Section::make(__('Image'))
                    ->components([
                        FileUpload::make('image_path')
                            ->label(__('Package image'))
                            ->image()
                            ->disk('public')
                            ->directory('packages')
                            ->imageEditor()
                            ->imagePreviewHeight('180')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Settings'))
                    ->columns(3)
                    ->components([
                        Toggle::make('is_featured')
                            ->label(__('Featured'))
                            ->inline(false),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),

                        TextInput::make('sort_order')
                            ->label(__('Sort order'))
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
