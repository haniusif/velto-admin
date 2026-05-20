<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Account'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('email')->label(__('Email'))->copyable(),
                        TextEntry::make('email_verified_at')
                            ->label(__('Email verified at'))
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label(__('Created'))
                            ->dateTime('Y-m-d H:i'),
                    ]),

                Section::make(__('Roles'))
                    ->components([
                        TextEntry::make('roles.name')
                            ->label(__('Roles'))
                            ->badge()
                            ->placeholder(__('No roles assigned')),
                    ]),
            ]);
    }
}
