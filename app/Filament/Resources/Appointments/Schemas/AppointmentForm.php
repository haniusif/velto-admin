<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('worker_id')
                    ->label(__('Assigned worker'))
                    ->relationship('worker', 'name')
                    ->searchable()
                    ->preload(),

                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'confirmed' => __('Confirmed'),
                        'on_the_way' => __('On the way'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ])
                    ->required()
                    ->default('confirmed'),

                DateTimePicker::make('scheduled_at')
                    ->label(__('Scheduled at'))
                    ->required(),

                Select::make('vehicle_id')
                    ->label(__('Vehicle'))
                    ->relationship('vehicle', 'plate')
                    ->searchable(),

                Select::make('wash_package_id')
                    ->label(__('Service'))
                    ->relationship('washPackage', 'name')
                    ->searchable(),

                Select::make('time_slot_id')
                    ->label(__('Time slot'))
                    ->relationship('timeSlot', 'id'),

                TextInput::make('service_name')
                    ->label(__('Service name (snapshot)')),

                TextInput::make('vehicle_label')
                    ->label(__('Vehicle (snapshot)')),

                TextInput::make('address_label')
                    ->label(__('Address')),

                Select::make('payment_method')
                    ->label(__('Payment method'))
                    ->options([
                        'wallet' => __('Wallet'),
                        'card' => __('Card'),
                        'apple_pay' => __('Apple Pay'),
                    ])
                    ->required()
                    ->default('wallet'),

                Select::make('payment_status')
                    ->label(__('Payment status'))
                    ->options([
                        'pending' => __('Pending'),
                        'paid' => __('Paid'),
                        'refunded' => __('Refunded'),
                        'refund_pending' => __('Refund pending'),
                    ])
                    ->required()
                    ->default('pending'),

                TextInput::make('base_price')
                    ->label(__('Base price'))
                    ->numeric()
                    ->prefix('SAR')
                    ->default(0),

                TextInput::make('addons_total')
                    ->label(__('Add-ons total'))
                    ->numeric()
                    ->prefix('SAR')
                    ->default(0),

                TextInput::make('total_price')
                    ->label(__('Total'))
                    ->numeric()
                    ->prefix('SAR')
                    ->required()
                    ->default(0),

                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),

                DateTimePicker::make('cancelled_at')
                    ->label(__('Cancelled at')),

                DateTimePicker::make('completed_at')
                    ->label(__('Completed at')),
            ]);
    }
}
