<?php

namespace App\Filament\Resources\Workers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('phone')->label(__('Phone'))->copyable(),
                        TextEntry::make('email')->label(__('Email'))->placeholder('—')->copyable(),
                        TextEntry::make('national_id')->label(__('National ID / Iqama'))->placeholder('—'),
                        TextEntry::make('city')->label(__('City')),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'suspended' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                    ]),

                Section::make(__('Employment'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('hire_date')->label(__('Hire date'))->date('Y-m-d')->placeholder('—'),
                        TextEntry::make('rating')->label(__('Rating'))->suffix(' / 5')->placeholder('—'),
                        TextEntry::make('created_at')->label(__('Created'))->dateTime('Y-m-d H:i'),
                    ]),

                Section::make(__('Notes'))
                    ->components([
                        TextEntry::make('notes')->label(__('Notes'))->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
