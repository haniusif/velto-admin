<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\CustomerPackage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * The plans a customer has bought and how much of each is left.
 *
 * Read-only: visits remaining is consumed by booking and restored by
 * cancellation, so editing the count here would silently disagree with the
 * bookings that spent it.
 */
class PackagesRelationManager extends RelationManager
{
    protected static string $relationship = 'packages';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Plans');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('washPackage.name')->label(__('Plan'))->placeholder('—'),
                TextColumn::make('vehicle.model')->label(__('Car'))->placeholder(__('Any car')),
                TextColumn::make('visits_remaining')
                    ->label(__('Visits left'))
                    // effectiveStatus()/visitsRemaining() carry the expiry and
                    // consumption rules; reading the raw column would show a
                    // balance on a plan that has already lapsed.
                    ->state(fn (CustomerPackage $record): int => $record->visitsRemaining())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->state(fn (CustomerPackage $record): string => $record->effectiveStatus())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CustomerPackage::STATUS_ACTIVE => 'success',
                        CustomerPackage::STATUS_EXPIRED => 'warning',
                        CustomerPackage::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
                TextColumn::make('expires_at')->label(__('Expires'))->date('Y-m-d')->placeholder('—'),
                TextColumn::make('created_at')->label(__('Bought'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
