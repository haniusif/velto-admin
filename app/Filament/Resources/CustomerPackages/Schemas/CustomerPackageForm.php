<?php

namespace App\Filament\Resources\CustomerPackages\Schemas;

use App\Models\CustomerPackage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Only the levers ops legitimately need: comp a visit, extend a window, or
 * cancel a plan. Who bought what, for which car, and what they paid are part
 * of the purchase record and are not editable here.
 */
class CustomerPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Purchase'))
                    ->description(__('Recorded at purchase — not editable.'))
                    ->columns(3)
                    ->components([
                        TextInput::make('customer.name')->label(__('Customer'))->disabled(),
                        TextInput::make('washPackage.name')->label(__('Plan'))->disabled(),
                        TextInput::make('price_paid')->label(__('Paid'))->suffix('SAR')->disabled(),
                        TextInput::make('payment_method')->label(__('Method'))->disabled(),
                        TextInput::make('payment_status')->label(__('Payment status'))->disabled(),
                        TextInput::make('starts_at')->label(__('Started'))->disabled(),
                    ]),

                Section::make(__('Entitlement'))
                    ->columns(3)
                    ->components([
                        TextInput::make('visits_total')
                            ->label(__('Visits included'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('visits_used')
                            ->label(__('Visits used'))
                            ->helperText(__('Lower this to give a visit back.'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->label(__('Expires'))
                            ->helperText(__('Clear to make the plan never expire.'))
                            ->seconds(false),

                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                CustomerPackage::STATUS_PENDING => __('Awaiting payment'),
                                CustomerPackage::STATUS_ACTIVE => __('Active'),
                                CustomerPackage::STATUS_EXPIRED => __('Expired'),
                                CustomerPackage::STATUS_CANCELLED => __('Cancelled'),
                            ])
                            ->native(false)
                            ->required(),
                    ]),
            ]);
    }
}
