<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Models\PromoCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

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
                            //
                            // The parameter MUST be named $state: Filament injects
                            // closure arguments by name, so a differently-named
                            // parameter receives null and every code was saved as an
                            // empty string.
                            ->dehydrateStateUsing(fn (?string $state) => mb_strtoupper(trim((string) $state)))
                            // PromoCode::findByCode matches on UPPER(code), so uniqueness has
                            // to be checked the same way. Left to the default, "save10" saves
                            // alongside "SAVE10" on any case-sensitive collation, and one of
                            // the two becomes unreachable.
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $state) => $rule
                                ->where(fn ($query) => $query
                                    ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim((string) $state))]))),

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
                            ->minValue(0.01)
                            // Nothing stopped a 500% code before this; the discount is applied
                            // to the total, so that pays the customer to book.
                            ->maxValue(fn ($get) => $get('type') === PromoCode::TYPE_PERCENT ? 100 : null)
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
                        DateTimePicker::make('expires_at')
                            ->label(__('Expires'))
                            ->seconds(false)
                            // withinWindow() requires starts_at past AND expires_at future;
                            // reversed dates make a code that is never redeemable and gives
                            // no clue why.
                            ->after('starts_at'),
                    ]),
            ]);
    }
}
