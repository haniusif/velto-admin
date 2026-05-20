<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
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

                        TextInput::make('city')
                            ->label(__('City'))
                            ->default('Riyadh')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('avatar_url')
                            ->label(__('Avatar URL'))
                            ->url()
                            ->maxLength(255),

                        DateTimePicker::make('joined_at')
                            ->label(__('Joined at'))
                            ->seconds(false)
                            ->native(false),
                    ]),

                Section::make(__('Preferences'))
                    ->columns(2)
                    ->components([
                        Select::make('preferred_language')
                            ->label(__('Preferred language'))
                            ->options([
                                'ar' => __('Arabic'),
                                'en' => __('English'),
                            ])
                            ->default('ar')
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'active' => __('Active'),
                                'inactive' => __('Inactive'),
                                'blocked' => __('Blocked'),
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
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
