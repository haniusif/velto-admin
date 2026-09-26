<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    public function getTitle(): string
    {
        return __('Order #:id', ['id' => $this->getRecord()->getKey()]);
    }

    protected function getHeaderActions(): array
    {
        // The same assign and cancel the list offers per row, so an operator
        // who opened the order to check it can act without going back.
        return [
            AppointmentsTable::autoAssignAction(),
            EditAction::make(),
            AppointmentsTable::cancelAction(),
        ];
    }
}
