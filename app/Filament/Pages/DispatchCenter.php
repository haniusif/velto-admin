<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Services\Dispatch\DispatchService;
use App\Services\Dispatch\DispatchStats;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class DispatchCenter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.dispatch-center';

    public static function getNavigationLabel(): string
    {
        return __('Dispatch Center');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Operations');
    }

    public function getTitle(): string
    {
        return __('Dispatch Center');
    }

    /** @return array<string,int|float> */
    public function kpis(): array
    {
        return app(DispatchStats::class)->kpis();
    }

    public function roster(): Collection
    {
        return app(DispatchStats::class)->roster();
    }

    public function waitingQueue(): Collection
    {
        return app(DispatchStats::class)->waitingQueue();
    }

    /** Livewire action: auto-assign a waiting job to the best eligible worker. */
    public function autoAssign(int $id): void
    {
        $appointment = Appointment::find($id);
        if ($appointment === null) {
            return;
        }

        $worker = app(DispatchService::class)->autoAssign($appointment);

        $worker
            ? Notification::make()->success()
                ->title(__('Assigned to :name', ['name' => $worker->name]))->send()
            : Notification::make()->warning()
                ->title(__('No eligible worker'))
                ->body(__('Still waiting — try again once a worker frees up.'))->send();
    }
}
