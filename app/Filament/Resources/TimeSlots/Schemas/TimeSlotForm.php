<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('When'))
                    ->columns(3)
                    ->components([
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native(false)
                            ->minDate(today()),

                        TimePicker::make('start_time')
                            ->label(__('Start'))
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('end_time')
                            ->label(__('End'))
                            ->seconds(false)
                            ->required()
                            ->after('start_time'),
                    ]),

                Section::make(__('Capacity'))
                    ->columns(3)
                    ->components([
                        TextInput::make('capacity')
                            ->label(__('Capacity'))
                            ->helperText(__('How many parallel bookings this slot can hold.'))
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        TextInput::make('booked_count')
                            ->label(__('Booked'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
