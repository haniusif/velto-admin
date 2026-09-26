<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\PromoCodes\PromoCodeResource;
use App\Filament\Resources\Workers\WorkerResource;
use App\Models\Appointment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        // Was one flat list of twenty-five fields, raw ids included, and never
        // said who the worker was. Now: the job and where it is on the left,
        // the people and the money — what an operator acts on — on the right.
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    self::bookingSection(),
                    self::locationSection(),
                    self::timelineSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    self::statusSection(),
                    self::peopleSection(),
                    self::paymentSection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function bookingSection(): Section
    {
        return Section::make(__('Booking'))
            ->icon('heroicon-o-sparkles')
            ->columns(2)
            ->components([
                TextEntry::make('service_name')
                    ->label(__('Service'))
                    ->weight(FontWeight::SemiBold)
                    ->size(TextSize::Large)
                    ->belowContent(fn (Appointment $r): ?string => $r->service_name_ar)
                    ->placeholder('-'),
                TextEntry::make('scheduled_at')->timezone(config('app.timezone'))
                    ->label(__('Scheduled at'))
                    ->dateTime()
                    ->weight(FontWeight::SemiBold)
                    ->size(TextSize::Large)
                    ->icon('heroicon-o-calendar-days')
                    ->belowContent(fn (Appointment $r): ?string => $r->scheduled_at?->diffForHumans()),
                TextEntry::make('vehicle_label')
                    ->label(__('Vehicle'))
                    ->icon('heroicon-o-truck')
                    ->placeholder('-'),
                TextEntry::make('washPackage.name')
                    ->label(__('Wash package'))
                    ->placeholder('-'),
                RepeatableEntry::make('add_ons')
                    ->label(__('Add-ons'))
                    ->placeholder(__('No add-ons'))
                    ->contained(false)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')->hiddenLabel(),
                        TextEntry::make('extra_price')->hiddenLabel()->money('SAR')->alignEnd(),
                    ]),
                TextEntry::make('notes')
                    ->label(__('Notes'))
                    ->placeholder(__('No notes'))
                    ->columnSpanFull(),
            ]);
    }

    private static function locationSection(): Section
    {
        return Section::make(__('Location'))
            ->icon('heroicon-o-map-pin')
            ->columns(3)
            ->components([
                TextEntry::make('address_label')
                    ->label(__('Address'))
                    ->placeholder('-'),
                TextEntry::make('area.name')
                    ->label(__('Area'))
                    ->placeholder('-'),
                TextEntry::make('zone.name')
                    ->label(__('Zone'))
                    ->badge()
                    ->placeholder('-'),
                ViewEntry::make('map')
                    ->hiddenLabel()
                    ->view('filament.infolists.appointment-map')
                    ->columnSpanFull(),
            ]);
    }

    private static function timelineSection(): Section
    {
        // Status stamps, payments, dispatch decisions and the review on one
        // rail, so "why was this late?" is answered without opening four
        // screens and lining the times up by hand.
        return Section::make(__('Timeline'))
            ->icon('heroicon-o-clock')
            ->collapsible()
            ->components([
                ViewEntry::make('timeline')
                    ->hiddenLabel()
                    ->view('filament.infolists.appointment-timeline')
                    ->columnSpanFull(),
            ]);
    }

    private static function statusSection(): Section
    {
        return Section::make(__('Status'))
            ->icon('heroicon-o-signal')
            ->columns(2)
            ->components([
                TextEntry::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => AppointmentsTable::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => __(ucwords(str_replace('_', ' ', $state)))),
                TextEntry::make('payment_status')
                    ->label(__('Payment status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'refunded' => 'warning',
                        'refund_pending' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst(str_replace('_', ' ', $state)))),
                TextEntry::make('dispatch_state')
                    ->label(__('Dispatch'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => __(ucfirst(str_replace('_', ' ', $state))))
                    ->visible(fn (Appointment $r): bool => filled($r->dispatch_state))
                    ->columnSpanFull(),
            ]);
    }

    private static function peopleSection(): Section
    {
        return Section::make(__('People'))
            ->icon('heroicon-o-users')
            ->components([
                TextEntry::make('customer.name')
                    ->label(__('Customer'))
                    ->icon('heroicon-o-user')
                    ->weight(FontWeight::SemiBold)
                    ->color('primary')
                    ->belowContent(fn (Appointment $r) => self::phoneLink($r->customer?->phone))
                    ->url(fn (Appointment $r): ?string => $r->customer_id
                        ? CustomerResource::getUrl('view', ['record' => $r->customer_id])
                        : null),
                TextEntry::make('worker.name')
                    ->label(__('Worker'))
                    ->icon('heroicon-o-wrench')
                    ->weight(FontWeight::SemiBold)
                    ->color(fn (Appointment $r): string => $r->worker_id ? 'primary' : 'warning')
                    ->placeholder(__('Unassigned'))
                    ->belowContent(fn (Appointment $r) => self::phoneLink($r->worker?->phone))
                    ->url(fn (Appointment $r): ?string => $r->worker_id
                        ? WorkerResource::getUrl('view', ['record' => $r->worker_id])
                        : null),
            ]);
    }

    private static function paymentSection(): Section
    {
        return Section::make(__('Payment'))
            ->icon('heroicon-o-banknotes')
            ->columns(2)
            ->components([
                TextEntry::make('payment_method')
                    ->label(__('Payment method'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'primary',
                        'card' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucwords(str_replace('_', ' ', $state))))
                    ->columnSpanFull(),
                TextEntry::make('base_price')
                    ->label(__('Base price'))
                    ->money('SAR'),
                TextEntry::make('addons_total')
                    ->label(__('Add-ons total'))
                    ->money('SAR'),
                TextEntry::make('discount_total')
                    ->label(__('Discount'))
                    ->money('SAR')
                    ->prefix('−')
                    ->color('success')
                    ->visible(fn (Appointment $r): bool => (float) $r->discount_total > 0),
                TextEntry::make('promoCode.code')
                    ->label(__('Promo code'))
                    ->badge()
                    ->color('success')
                    ->url(fn (Appointment $r): ?string => $r->promo_code_id
                        ? PromoCodeResource::getUrl('view', ['record' => $r->promo_code_id])
                        : null)
                    ->visible(fn (Appointment $r): bool => filled($r->promo_code_id)),
                TextEntry::make('total_price')
                    ->label(__('Total'))
                    ->money('SAR')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Large)
                    ->columnSpanFull(),
                TextEntry::make('walletTransaction.id')
                    ->label(__('Wallet transaction'))
                    ->prefix('#')
                    ->visible(fn (Appointment $r): bool => filled($r->wallet_transaction_id))
                    ->columnSpanFull(),
            ]);
    }

    /** A tap-to-call number under a person's name; phones are how dispatch reaches them. */
    private static function phoneLink(?string $phone): ?HtmlString
    {
        if (blank($phone)) {
            return null;
        }

        return new HtmlString('<a href="tel:'.e($phone).'" dir="ltr" style="text-decoration: underline">'.e($phone).'</a>');
    }
}
