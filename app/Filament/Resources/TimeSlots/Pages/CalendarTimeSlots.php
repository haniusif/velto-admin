<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use App\Models\TimeSlot;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class CalendarTimeSlots extends Page
{
    protected static string $resource = TimeSlotResource::class;

    protected string $view = 'filament.resources.time-slots.calendar';

    public function getTitle(): string
    {
        return __('Available time slots');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label(__('List view'))
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(TimeSlotResource::getUrl('list')),
            Action::make('create')
                ->label(__('New time slot'))
                ->icon('heroicon-o-plus')
                ->url(TimeSlotResource::getUrl('create')),
        ];
    }

    /** FullCalendar events built from the time-slot rows. */
    public function calendarEvents(): array
    {
        return TimeSlot::query()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function (TimeSlot $s) {
                $date = $s->date?->toDateString();
                $start = substr((string) $s->start_time, 0, 5);
                $end = substr((string) $s->end_time, 0, 5);
                $available = max(0, (int) $s->capacity - (int) $s->booked_count);
                $full = $available <= 0;

                $color = ! $s->is_active
                    ? '#9ca3af'          // inactive
                    : ($full ? '#ef4444' // fully booked
                        : '#16a34a');    // has availability

                return [
                    'id' => $s->id,
                    'title' => "{$start}–{$end} · ".(int) $s->booked_count.'/'.(int) $s->capacity,
                    'start' => "{$date}T{$s->start_time}",
                    'end' => "{$date}T{$s->end_time}",
                    'url' => TimeSlotResource::getUrl('edit', ['record' => $s->id]),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                ];
            })
            ->all();
    }
}
