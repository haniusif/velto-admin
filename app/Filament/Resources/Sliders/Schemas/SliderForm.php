<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Image'))
                    ->components([
                        FileUpload::make('image_path')
                            ->label(__('Slider image'))
                            ->image()
                            ->disk('public')
                            ->directory('sliders')
                            ->imageEditor()
                            ->imagePreviewHeight('220')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Settings'))
                    ->columns(2)
                    ->components([
                        TextInput::make('sort_order')
                            ->label(__('Sort order'))
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
