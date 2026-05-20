<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('group')->label(__('Group'))->default('general')->required(),
                TextInput::make('key')->label(__('Key'))->required()->unique(ignoreRecord: true),
                TextInput::make('label')->label(__('Label')),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'string' => 'String',
                        'text' => 'Text (multiline)',
                        'url' => 'URL',
                        'email' => 'Email',
                        'tel' => 'Telephone',
                        'bool' => 'Boolean',
                        'number' => 'Number',
                    ])
                    ->default('string')
                    ->native(false)
                    ->required(),
                Textarea::make('value')->label(__('Value'))->rows(3)->columnSpanFull(),
            ])
            ->columns(2);
    }
}
