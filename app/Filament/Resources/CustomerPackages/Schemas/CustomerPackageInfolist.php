<?php

namespace App\Filament\Resources\CustomerPackages\Schemas;

use App\Models\CustomerPackage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerPackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Plan'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('customer.name')->label(__('Customer'))->badge()->color('primary'),
                        TextEntry::make('washPackage.name')->label(__('Plan')),
                        TextEntry::make('vehicle.plate')->label(__('Vehicle'))->placeholder('—'),

                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->state(fn (CustomerPackage $r): string => $r->effectiveStatus()),

                        TextEntry::make('visits')
                            ->label(__('Visits left'))
                            ->state(fn (CustomerPackage $r): string => $r->visitsRemaining().' / '.$r->visits_total),

                        TextEntry::make('expires_at')->label(__('Expires'))->dateTime('Y-m-d H:i')->placeholder('—'),
                    ]),

                Section::make(__('Payment'))
                    ->columns(4)
                    ->components([
                        TextEntry::make('price_paid')->label(__('Paid'))->money('SAR'),
                        TextEntry::make('payment_method')->label(__('Method'))->badge(),
                        TextEntry::make('payment_status')->label(__('Payment status'))->badge(),
                        TextEntry::make('created_at')->label(__('Bought'))->dateTime('Y-m-d H:i'),
                    ]),
            ]);
    }
}
