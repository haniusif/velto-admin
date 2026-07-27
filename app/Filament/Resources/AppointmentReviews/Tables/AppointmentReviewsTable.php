<?php

namespace App\Filament\Resources\AppointmentReviews\Tables;

use App\Models\AppointmentReview;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('When'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('appointment_id')
                    ->label(__('Booking'))
                    ->prefix('#')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable(),

                TextColumn::make('worker.name')
                    ->label(__('Specialist'))
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('rating')
                    ->label(__('Rating'))
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state))
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('comment')
                    ->label(__('Comment'))
                    ->placeholder('—')
                    ->wrap()
                    ->limit(120),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label(__('Rating'))
                    ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),

                // The ones worth reading first.
                Filter::make('poor')
                    ->label(__('Needs attention (1–2 stars)'))
                    ->query(fn ($query) => $query->where('rating', '<=', 2)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
