<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Account'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? __('Leave blank to keep current password.')
                                : null),

                        DateTimePicker::make('email_verified_at')
                            ->label(__('Email verified at'))
                            ->seconds(false),
                    ]),

                Section::make(__('Roles & permissions'))
                    ->components([
                        Select::make('roles')
                            ->label(__('Roles'))
                            ->relationship('roles', 'name')
                            ->options(fn () => Role::query()->pluck('name', 'id'))
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}
