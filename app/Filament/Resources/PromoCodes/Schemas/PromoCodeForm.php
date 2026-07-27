<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Models\PromoCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Code'))
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(40)
                            // Stored uppercase so lookups are predictable and
                            // customers never lose to their keyboard case.
                            ->dehydrateStateUsing(fn (?string $s) => mb_strtoupper(trim((string) $s)))
                            ->unique(ignoreRecord: true),

                        Toggle::make('is_active')->label(__('Active'))->default(true)->inline(false),

                        TextInput::make('description')->label(__('Description'))->maxLength(255),
                        TextInput::make('description_ar')->label(__('Description (Arabic)'))->maxLength(255),
                    ]),

                Section::make(__('Discount'))
                    ->columns(3)
                    ->components([
                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                PromoCode::TYPE_PERCENT => __('Percentage'),
                                PromoCode::TYPE_FIXED => __('Fixed amount'),
                            ])
                            ->default(PromoCode::TYPE_PERCENT)
                            ->native(false)
                            ->live()
                            ->required(),

                        TextInput::make('value')
                            ->label(__('Value'))
                            ->numeric()
                            ->required()
                            ->suffix(fn ($get) => $get('type') === PromoCode::TYPE_PERCENT ? '%' : 'SAR'),

                        TextInput::make('max_discount')
                            ->label(__('Max discount'))
                            ->helperText(__('Strongly recommended for percentage codes.'))
                            ->numeric()
                            ->suffix('SAR')
                            ->visible(fn ($get) => $get('type') === PromoCode::TYPE_PERCENT),

                        TextInput::make('min_order_total')
                            ->label(__('Minimum order'))
                            ->numeric()
                            ->default(0)
                            ->suffix('SAR'),
                    ]),

                Section::make(__('Limits'))
                    ->columns(3)
                    ->components([
                        TextInput::make('usage_limit')
                            ->label(__('Total uses'))
                            ->helperText(__('Leave empty for unlimited.'))
                            ->numeric()
                            ->minValue(0),

                        TextInput::make('per_customer_limit')
                            ->label(__('Uses per customer'))
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->required(),

                        TextInput::make('used_count')
                            ->label(__('Used so far'))
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('starts_at')->label(__('Starts'))->seconds(false),
                        DateTimePicker::make('expires_at')->label(__('Expires'))->seconds(false),
                    ]),
            ]);
    }
}
