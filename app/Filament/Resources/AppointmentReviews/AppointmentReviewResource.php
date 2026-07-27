<?php

namespace App\Filament\Resources\AppointmentReviews;

use App\Filament\Resources\AppointmentReviews\Pages\ListAppointmentReviews;
use App\Filament\Resources\AppointmentReviews\Tables\AppointmentReviewsTable;
use App\Models\AppointmentReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Customer feedback on finished jobs. Read-only: a review is what the customer
 * said, so it is neither authored nor edited from the panel.
 */
class AppointmentReviewResource extends Resource
{
    protected static ?string $model = AppointmentReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('Customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('Reviews');
    }

    public static function getModelLabel(): string
    {
        return __('Review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Reviews');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AppointmentReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListAppointmentReviews::route('/')];
    }
}
