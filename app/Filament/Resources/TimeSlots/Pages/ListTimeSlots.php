<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimeSlots extends ListRecords
{
    protected static string $resource = TimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendar')
                ->label(__('Calendar view'))
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->url(TimeSlotResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
