<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Models\PromoCode;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Code'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('code')->label(__('Code'))->copyable()->weight('bold'),
                        TextEntry::make('value')
                            ->label(__('Discount'))
                            ->badge()
                            ->formatStateUsing(fn ($state, PromoCode $r): string => $r->type === PromoCode::TYPE_PERCENT
                                ? rtrim(rtrim((string) $state, '0'), '.').'%'.($r->max_discount ? ' ('.__('max').' '.(float) $r->max_discount.' SAR)' : '')
                                : (float) $state.' SAR'),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                        TextEntry::make('description')->label(__('Description'))->placeholder('—'),
                        TextEntry::make('description_ar')->label(__('Description (AR)'))->placeholder('—'),
                        TextEntry::make('min_order_total')->label(__('Min order'))->money('SAR'),
                    ]),

                Section::make(__('Usage'))
                    ->columns(4)
                    ->components([
                        // The headline number: what it has cost so far.
                        TextEntry::make('used_count')
                            ->label(__('Times used'))
                            ->formatStateUsing(fn ($state, PromoCode $r): string => $r->usage_limit === null
                                ? (string) $state
                                : $state.' / '.$r->usage_limit),
                        TextEntry::make('redemptions_sum_amount')
                            ->label(__('Discount given'))
                            ->state(fn (PromoCode $r): float => (float) $r->redemptions()->sum('amount'))
                            ->money('SAR'),
                        TextEntry::make('per_customer_limit')->label(__('Uses per customer')),
                        TextEntry::make('customers_count')
                            ->label(__('Customers'))
                            ->state(fn (PromoCode $r): int => (int) $r->redemptions()->distinct('customer_id')->count('customer_id')),
                    ]),

                Section::make(__('Window'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('starts_at')->timezone(config('app.timezone'))->label(__('Starts'))->dateTime('Y-m-d g:i A')->placeholder('—'),
                        TextEntry::make('expires_at')->timezone(config('app.timezone'))->label(__('Expires'))->dateTime('Y-m-d g:i A')->placeholder('—'),
                        TextEntry::make('created_at')->timezone(config('app.timezone'))->label(__('Created'))->dateTime('Y-m-d'),
                    ]),
            ]);
    }
}
