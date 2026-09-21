<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Models\SupportTicket;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // What the customer wrote is read-only here: the agent answers
                // it, they don't rewrite it.
                Section::make(__('From the customer'))
                    ->schema([
                        TextEntry::make('customer.name')->label(__('Customer')),
                        TextEntry::make('customer.phone')->label(__('Phone')),
                        TextEntry::make('type')
                            ->label(__('Type'))
                            ->formatStateUsing(fn (string $state): string => SupportTicket::typeLabels()[$state] ?? $state),
                        TextEntry::make('appointment_id')
                            ->label(__('Booking'))
                            ->prefix('#')
                            ->placeholder('—'),
                        TextEntry::make('subject')->label(__('Subject'))->columnSpanFull(),
                        TextEntry::make('message')->label(__('Message'))->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Reply'))
                    ->schema([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(SupportTicket::statusLabels())
                            ->required()
                            ->native(false),
                        Textarea::make('admin_reply')
                            ->label(__('Reply to customer'))
                            ->helperText(__('Sent to the customer as a notification when saved.'))
                            ->rows(6)
                            ->maxLength(4000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
