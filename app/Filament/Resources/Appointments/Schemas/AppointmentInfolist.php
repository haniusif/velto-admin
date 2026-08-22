<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                // The coordinates above answer "where is this?" only once you
                // have pasted them somewhere else. Drawn with the same
                // MapEntry the areas, zones and cities screens use, so the
                // panel has one kind of map rather than two.
                MapEntry::make('location')
                    ->label(__('Location'))
                    ->columnSpanFull()
                    ->extraStyles(['min-height: 360px', 'border-radius: 16px'])
                    ->defaultLocation(24.7136, 46.6753)
                    ->zoom(15)
                    ->showMarker(true)
                    ->markerColor('#8863E5')
                    ->getStateUsing(fn ($record) => self::point($record))
                    // A booking with no pin would otherwise render as central
                    // Riyadh with no marker, which reads as a real location.
                    ->visible(fn ($record) => self::point($record) !== null),
                ViewEntry::make('map_actions')
                    ->label('')
                    ->view('filament.infolists.appointment-map')
                    ->columnSpanFull(),
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
                    ->numeric(),
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

    /**
     * The booking's pin, or null when there isn't one.
     *
     * 0,0 counts as absent: it is a real coordinate in the Gulf of Guinea, so
     * a marker there would look like an answer rather than missing data.
     */
    private static function point($record): ?array
    {
        $lat = is_numeric($record?->latitude) ? (float) $record->latitude : null;
        $lng = is_numeric($record?->longitude) ? (float) $record->longitude : null;

        if ($lat === null || $lng === null || ($lat === 0.0 && $lng === 0.0)) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }
}
