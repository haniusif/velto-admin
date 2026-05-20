<?php

namespace App\Filament\Resources\Workers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->prefix('+966')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(32),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('national_id')
                            ->label(__('National ID / Iqama'))
                            ->maxLength(32),

                        TextInput::make('city')
                            ->label(__('City'))
                            ->default('Riyadh')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('avatar_url')
                            ->label(__('Avatar URL'))
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make(__('Employment'))
                    ->columns(3)
                    ->components([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'active' => __('Active'),
                                'inactive' => __('Inactive'),
                                'suspended' => __('Suspended'),
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),

                        DatePicker::make('hire_date')
                            ->label(__('Hire date'))
                            ->native(false),

                        TextInput::make('rating')
                            ->label(__('Rating'))
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(5)
                            ->suffix('/5'),
                    ]),

                Section::make(__('Notes'))
                    ->components([
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
