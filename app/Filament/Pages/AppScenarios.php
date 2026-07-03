<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class AppScenarios extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.app-scenarios';

    public static function getNavigationLabel(): string
    {
        return __('App scenarios');
    }

    public function getTitle(): string
    {
        return __('App scenarios');
    }
}
