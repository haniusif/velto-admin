<?php

namespace App\Filament\Widgets;

use App\Models\CustomerNotification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentNotificationsTable extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('Latest customer notifications');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CustomerNotification::query()
                ->with('customer:id,name,phone')
                ->latest('id')
                ->limit(8))
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Sent'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable(),
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
                    ->limit(60),
                IconColumn::make('read_at')
                    ->label(__('Read'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->read_at !== null),
            ])
            ->paginated(false);
    }
}
