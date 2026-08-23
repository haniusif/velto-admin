<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Sat eleventh in a flat list of twenty-five fields, which is
                // nobody's idea of where to look for a location. A dispatcher
                // opens this page to see where the job is, so it goes first.
                Section::make(__('Location'))
                    ->components([
                        ViewEntry::make('map')
                            ->hiddenLabel()
                            ->view('filament.infolists.appointment-map')
                            ->columnSpanFull(),
                    ]),
                TextEntry::make('customer.name')
                    ->label(__('Customer')),
                TextEntry::make('vehicle.name')
                    ->label(__('Vehicle'))
                    ->placeholder('-'),
                TextEntry::make('washPackage.name')
                    ->label(__('Wash package'))
                    ->placeholder('-'),
                TextEntry::make('timeSlot.id')
                    ->label(__('Time slot'))
                    ->placeholder('-'),
                TextEntry::make('walletTransaction.id')
                    ->label(__('Wallet transaction'))
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label(__('Status')),
                TextEntry::make('scheduled_at')
                    ->label(__('Scheduled at'))
                    ->dateTime(),
                TextEntry::make('address_label')
                    ->label(__('Address'))
                    ->placeholder('-'),
                TextEntry::make('latitude')
                    ->label(__('Latitude'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->label(__('Longitude'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('area.name')
                    ->label(__('Area'))
                    ->placeholder('-'),
                TextEntry::make('zone.name')
                    ->label(__('Zone'))
                    ->placeholder('-'),
                TextEntry::make('service_name')
                    ->label(__('Service name'))
                    ->placeholder('-'),
                TextEntry::make('service_name_ar')
                    ->label(__('Service name (Arabic)'))
                    ->placeholder('-'),
                TextEntry::make('vehicle_label')
                    ->label(__('Vehicle (snapshot)'))
                    ->placeholder('-'),
                TextEntry::make('base_price')
                    ->label(__('Base price'))
                    ->money(),
                TextEntry::make('addons_total')
                    ->label(__('Add-ons total'))
                    // Was ->numeric(), so a money field printed a bare "0"
                    // between two neighbours showing currency.
                    ->money(),
                TextEntry::make('total_price')
                    ->label(__('Total'))
                    ->money(),
                TextEntry::make('payment_method')
                    ->label(__('Payment method')),
                TextEntry::make('payment_status')
                    ->label(__('Payment status')),
                TextEntry::make('notes')
                    ->label(__('Notes'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('cancelled_at')
                    ->label(__('Cancelled at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->label(__('Completed at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
