<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Models\PromoCode;
use App\Support\BookingTime;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label(__('Code'))->searchable()->copyable()->weight('bold'),

                TextColumn::make('value')
                    ->label(__('Discount'))
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state, PromoCode $r): string => $r->type === PromoCode::TYPE_PERCENT
                        ? rtrim(rtrim((string) $state, '0'), '.').'%'.($r->max_discount ? ' (max '.(float) $r->max_discount.')' : '')
                        : (float) $state.' SAR'),

                TextColumn::make('min_order_total')
                    ->label(__('Min order'))
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('used_count')
                    ->label(__('Used'))
                    ->formatStateUsing(fn ($state, PromoCode $r): string => $r->usage_limit === null
                        ? (string) $state
                        : $state.' / '.$r->usage_limit)
                    ->sortable(),

                TextColumn::make('expires_at')->timezone(config('app.timezone'))
                    ->label(__('Expires'))
                    ->dateTime('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),

                IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                // What is actually redeemable right now, which is rarely the
                // same as what is switched on.
                Filter::make('live')
                    ->label(__('Redeemable now'))
                    ->query(fn ($query) => $query
                        ->where('is_active', true)
                        // Naive Riyadh wall-clock columns, so meet them there
                        // rather than binding a UTC now() three hours off.
                        ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', BookingTime::nowWallClock()))
                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', BookingTime::nowWallClock()))
                        ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }
}
