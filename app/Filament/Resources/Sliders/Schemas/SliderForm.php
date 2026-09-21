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
                        // The app draws the card at 2:1, so the editor crops to
                        // that and the browser shrinks the file before upload —
                        // marketing was handing us 2.5 MB PNGs for a 350pt slot.
                        FileUpload::make('image_path')
                            ->label(__('Slider image'))
                            ->helperText(__('2:1 landscape, e.g. 1600×800. Anything else is cropped to fit.'))
                            ->image()
                            ->disk('public')
                            ->directory('sliders')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['2:1'])
                            ->imageCropAspectRatio('2:1')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1600')
                            ->imageResizeTargetHeight('800')
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
