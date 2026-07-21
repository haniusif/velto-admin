<?php

namespace App\Filament\Pages;

use App\Services\Dispatch\DispatchStats;
use BackedEnum;
use Filament\Pages\Page;

class DispatchBoard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.dispatch-board';

    public static function getNavigationLabel(): string
    {
        return __('Dispatch Board');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Operations');
    }

    public function getTitle(): string
    {
        return __('Dispatch Board');
    }

    /** @return array<string,\Illuminate\Support\Collection> */
    public function board(): array
    {
        return app(DispatchStats::class)->board();
    }
}
