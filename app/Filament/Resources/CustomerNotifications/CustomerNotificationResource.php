<?php

namespace App\Filament\Resources\CustomerNotifications;

use App\Filament\Resources\CustomerNotifications\Pages\CreateCustomerNotification;
use App\Filament\Resources\CustomerNotifications\Pages\EditCustomerNotification;
use App\Filament\Resources\CustomerNotifications\Pages\ListCustomerNotifications;
use App\Filament\Resources\CustomerNotifications\Pages\ViewCustomerNotification;
use App\Filament\Resources\CustomerNotifications\Schemas\CustomerNotificationForm;
use App\Filament\Resources\CustomerNotifications\Schemas\CustomerNotificationInfolist;
use App\Filament\Resources\CustomerNotifications\Tables\CustomerNotificationsTable;
use App\Models\CustomerNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerNotificationResource extends Resource
{
    protected static ?string $model = CustomerNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('Notifications');
    }

    public static function getModelLabel(): string
    {
        return __('Notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Notifications');
    }

    public static function getNavigationBadge(): ?string
    {
        $unread = static::getModel()::query()->whereNull('read_at')->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerNotificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerNotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerNotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerNotifications::route('/'),
            'create' => CreateCustomerNotification::route('/create'),
            'view' => ViewCustomerNotification::route('/{record}'),
            'edit' => EditCustomerNotification::route('/{record}/edit'),
        ];
    }
}
