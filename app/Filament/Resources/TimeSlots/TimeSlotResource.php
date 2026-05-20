<?php

namespace App\Filament\Resources\TimeSlots;

use App\Filament\Resources\TimeSlots\Pages\CreateTimeSlot;
use App\Filament\Resources\TimeSlots\Pages\EditTimeSlot;
use App\Filament\Resources\TimeSlots\Pages\ListTimeSlots;
use App\Filament\Resources\TimeSlots\Pages\ViewTimeSlot;
use App\Filament\Resources\TimeSlots\Schemas\TimeSlotForm;
use App\Filament\Resources\TimeSlots\Schemas\TimeSlotInfolist;
use App\Filament\Resources\TimeSlots\Tables\TimeSlotsTable;
use App\Models\TimeSlot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimeSlotResource extends Resource
{
    protected static ?string $model = TimeSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'date';

    protected static ?int $navigationSort = 22;

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('Time slots');
    }

    public static function getModelLabel(): string
    {
        return __('Time slot');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Time slots');
    }

    public static function form(Schema $schema): Schema
    {
        return TimeSlotForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TimeSlotInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeSlotsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeSlots::route('/'),
            'create' => CreateTimeSlot::route('/create'),
            'view' => ViewTimeSlot::route('/{record}'),
            'edit' => EditTimeSlot::route('/{record}/edit'),
        ];
    }
}
