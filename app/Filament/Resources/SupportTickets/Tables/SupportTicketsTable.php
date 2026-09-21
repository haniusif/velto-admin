<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Models\SupportTicket;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('When'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SupportTicket::typeLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        SupportTicket::TYPE_COMPLAINT => 'danger',
                        SupportTicket::TYPE_SUGGESTION => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable()
                    ->wrap()
                    ->limit(80),

                TextColumn::make('appointment_id')
                    ->label(__('Booking'))
                    ->prefix('#')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SupportTicket::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        SupportTicket::STATUS_OPEN => 'warning',
                        SupportTicket::STATUS_IN_PROGRESS => 'info',
                        SupportTicket::STATUS_RESOLVED => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(SupportTicket::statusLabels())
                    ->default(SupportTicket::STATUS_OPEN),
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(SupportTicket::typeLabels()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
