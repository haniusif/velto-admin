<?php

namespace App\Filament\Resources\CustomerPackages\Tables;

use App\Models\CustomerPackage;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Bought'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('washPackage.name')
                    ->label(__('Plan'))
                    ->searchable(),

                TextColumn::make('vehicle.plate')
                    ->label(__('Vehicle'))
                    ->placeholder('—')
                    ->description(fn (CustomerPackage $r): ?string => $r->vehicle
                        ? trim("{$r->vehicle->brand} {$r->vehicle->model}")
                        : null),

                // The stored status can lag a lapsed window, so show what the
                // model actually enforces rather than the raw column.
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->state(fn (CustomerPackage $r): string => $r->effectiveStatus())
                    ->color(fn (string $state): string => match ($state) {
                        CustomerPackage::STATUS_ACTIVE => 'success',
                        CustomerPackage::STATUS_PENDING => 'warning',
                        CustomerPackage::STATUS_EXPIRED => 'gray',
                        CustomerPackage::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('visits')
                    ->label(__('Visits left'))
                    ->state(fn (CustomerPackage $r): string => $r->visitsRemaining().' / '.$r->visits_total)
                    ->color(fn (CustomerPackage $r): string => $r->visitsRemaining() > 0 ? 'primary' : 'gray'),

                TextColumn::make('expires_at')
                    ->label(__('Expires'))
                    ->dateTime('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('price_paid')
                    ->label(__('Paid'))
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label(__('Method'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        CustomerPackage::STATUS_ACTIVE => __('Active'),
                        CustomerPackage::STATUS_PENDING => __('Awaiting payment'),
                        CustomerPackage::STATUS_EXPIRED => __('Expired'),
                        CustomerPackage::STATUS_CANCELLED => __('Cancelled'),
                    ]),

                // What support is usually asked about: a plan that is paid for
                // and still has visits, but whose window has run out.
                Filter::make('lapsed')
                    ->label(__('Lapsed with visits left'))
                    ->query(fn ($query) => $query
                        ->where('status', CustomerPackage::STATUS_ACTIVE)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now())
                        ->whereColumn('visits_used', '<', 'visits_total')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
