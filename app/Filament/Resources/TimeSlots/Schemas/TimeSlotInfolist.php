<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeSlotInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->components([
                        TextEntry::make('date')->label(__('Date'))->date('l, Y-m-d'),
                        TextEntry::make('start_time')->label(__('Start'))->time('H:i'),
                        TextEntry::make('end_time')->label(__('End'))->time('H:i'),
                        TextEntry::make('capacity')->label(__('Capacity')),
                        TextEntry::make('booked_count')->label(__('Booked')),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                    ]),
            ]);
    }
}
