<?php

namespace App\Filament\Resources\CustomerNotifications\Schemas;

use App\Models\Customer;
use App\Models\CustomerNotification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Recipient'))
                    ->components([
                        Select::make('customer_id')
                            ->label(__('Customer'))
                            ->options(fn () => Customer::query()
                                ->orderBy('name')
                                ->limit(500)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make(__('Message'))
                    ->columns(2)
                    ->components([
                        Select::make('kind')
                            ->label(__('Kind'))
                            ->options([
                                CustomerNotification::KIND_BOOKING => __('Booking'),
                                CustomerNotification::KIND_ON_THE_WAY => __('On the way'),
                                CustomerNotification::KIND_COMPLETED => __('Completed'),
                                CustomerNotification::KIND_PROMO => __('Promo'),
                            ])
                            ->required()
                            ->native(false),

                        DateTimePicker::make('read_at')
                            ->label(__('Read at'))
                            ->seconds(false)
                            ->native(false),

                        TextInput::make('title')
                            ->label(__('Title (English)'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('title_ar')
                            ->label(__('Title (Arabic)'))
                            ->maxLength(255)
                            ->columnSpan(1),

                        Textarea::make('body')
                            ->label(__('Body (English)'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('body_ar')
                            ->label(__('Body (Arabic)'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
