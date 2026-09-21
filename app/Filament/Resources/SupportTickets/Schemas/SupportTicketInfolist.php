<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Models\SupportTicket;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportTicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('From the customer'))
                    ->schema([
                        TextEntry::make('customer.name')->label(__('Customer')),
                        TextEntry::make('customer.phone')->label(__('Phone')),
                        TextEntry::make('type')
                            ->label(__('Type'))
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => SupportTicket::typeLabels()[$state] ?? $state),
                        TextEntry::make('appointment_id')
                            ->label(__('Booking'))
                            ->prefix('#')
                            ->placeholder('—'),
                        TextEntry::make('created_at')->label(__('When'))->dateTime('Y-m-d H:i'),
                        TextEntry::make('subject')->label(__('Subject'))->columnSpanFull(),
                        TextEntry::make('message')->label(__('Message'))->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Reply'))
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => SupportTicket::statusLabels()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                SupportTicket::STATUS_OPEN => 'warning',
                                SupportTicket::STATUS_IN_PROGRESS => 'info',
                                SupportTicket::STATUS_RESOLVED => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('replied_at')->label(__('Replied at'))->dateTime('Y-m-d H:i')->placeholder('—'),
                        TextEntry::make('admin_reply')->label(__('Reply to customer'))->placeholder('—')->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
