<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTimeSlot extends ViewRecord
{
    protected static string $resource = TimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
