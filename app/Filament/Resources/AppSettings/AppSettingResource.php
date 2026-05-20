<?php

namespace App\Filament\Resources\AppSettings;

use App\Filament\Resources\AppSettings\Pages\CreateAppSetting;
use App\Filament\Resources\AppSettings\Pages\EditAppSetting;
use App\Filament\Resources\AppSettings\Pages\ListAppSettings;
use App\Filament\Resources\AppSettings\Pages\ViewAppSetting;
use App\Filament\Resources\AppSettings\Schemas\AppSettingForm;
use App\Filament\Resources\AppSettings\Schemas\AppSettingInfolist;
use App\Filament\Resources\AppSettings\Tables\AppSettingsTable;
use App\Models\AppSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?int $navigationSort = 35;

    public static function getNavigationGroup(): ?string { return __('Lookups'); }
    public static function getNavigationLabel(): string { return __('App settings'); }
    public static function getModelLabel(): string { return __('Setting'); }
    public static function getPluralModelLabel(): string { return __('App settings'); }

    public static function form(Schema $schema): Schema { return AppSettingForm::configure($schema); }
    public static function infolist(Schema $schema): Schema { return AppSettingInfolist::configure($schema); }
    public static function table(Table $table): Table { return AppSettingsTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index' => ListAppSettings::route('/'),
            'create' => CreateAppSetting::route('/create'),
            'view' => ViewAppSetting::route('/{record}'),
            'edit' => EditAppSetting::route('/{record}/edit'),
        ];
    }
}
