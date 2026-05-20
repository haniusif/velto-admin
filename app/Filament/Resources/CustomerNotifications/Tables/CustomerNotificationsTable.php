<?php

namespace App\Filament\Resources\CustomerNotifications\Tables;

use App\Models\CustomerNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CustomerNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kind')
                    ->label(__('Kind'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'booking' => 'primary',
                        'on_the_way' => 'warning',
                        'completed' => 'success',
                        'promo' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'booking' => __('Booking'),
                        'on_the_way' => __('On the way'),
                        'completed' => __('Completed'),
                        'promo' => __('Promo'),
                        default => $state,
                    }),

                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->limit(60)
                    ->description(fn ($record) => $record->title_ar),

                IconColumn::make('read_at')
                    ->label(__('Read'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->read_at !== null),

                TextColumn::make('created_at')
                    ->label(__('Sent'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label(__('Kind'))
                    ->options([
                        'booking' => __('Booking'),
                        'on_the_way' => __('On the way'),
                        'completed' => __('Completed'),
                        'promo' => __('Promo'),
                    ]),

                TernaryFilter::make('read_at')
                    ->label(__('Read'))
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('mark_read')
                    ->label(__('Mark read'))
                    ->icon('heroicon-m-check')
                    ->visible(fn (CustomerNotification $r) => $r->read_at === null)
                    ->action(fn (CustomerNotification $r) => $r->update(['read_at' => now()])),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_read')
                        ->label(__('Mark all read'))
                        ->icon('heroicon-m-check')
                        ->action(fn (Collection $records) => $records->each(
                            fn (CustomerNotification $r) => $r->update(['read_at' => now()])
                        ))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
