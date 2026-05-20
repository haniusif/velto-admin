<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\CustomerNotification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomerNotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerNotifications';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Notifications');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('kind')
                    ->label(__('Kind'))
                    ->options([
                        CustomerNotification::KIND_BOOKING => __('Booking'),
                        CustomerNotification::KIND_ON_THE_WAY => __('On the way'),
                        CustomerNotification::KIND_COMPLETED => __('Completed'),
                        CustomerNotification::KIND_PROMO => __('Promo'),
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('title')->label(__('Title (English)'))->required()->maxLength(255),
                TextInput::make('title_ar')->label(__('Title (Arabic)'))->maxLength(255),
                Textarea::make('body')->label(__('Body (English)'))->rows(2)->columnSpan(2),
                Textarea::make('body_ar')->label(__('Body (Arabic)'))->rows(2)->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('created_at')->label(__('Sent'))->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('kind')->label(__('Kind'))->badge(),
                TextColumn::make('title')->label(__('Title'))->limit(60)->searchable(),
                IconColumn::make('read_at')
                    ->label(__('Read'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->read_at !== null),
            ])
            ->filters([
                SelectFilter::make('kind')->options([
                    'booking' => __('Booking'),
                    'on_the_way' => __('On the way'),
                    'completed' => __('Completed'),
                    'promo' => __('Promo'),
                ]),
                TernaryFilter::make('read_at')->label(__('Read'))->nullable(),
            ])
            ->headerActions([
                CreateAction::make()->label(__('Send notification')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
