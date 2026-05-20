<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AppSettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('group')->label(__('Group'))->badge(),
                TextEntry::make('key')->label(__('Key'))->copyable(),
                TextEntry::make('label')->label(__('Label'))->placeholder('—'),
                TextEntry::make('type')->label(__('Type'))->badge(),
                TextEntry::make('value')->label(__('Value'))->placeholder('—')->columnSpanFull(),
            ])
            ->columns(2);
    }
}
