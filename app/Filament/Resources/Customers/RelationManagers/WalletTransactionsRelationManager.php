<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\WalletTransaction;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletTransactions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Wallet');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('kind')
                    ->label(__('Kind'))
                    ->options([
                        WalletTransaction::KIND_TOP_UP => __('Top-up'),
                        WalletTransaction::KIND_BOOKING => __('Booking'),
                        WalletTransaction::KIND_REFUND => __('Refund'),
                        WalletTransaction::KIND_ADJUSTMENT => __('Adjustment'),
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('amount')
                    ->label(__('Amount'))
                    ->helperText(__('Positive = credit, negative = debit'))
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->suffix('SAR'),
                Textarea::make('note')->label(__('Note'))->rows(2)->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kind')
            ->columns([
                TextColumn::make('created_at')->label(__('When'))->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('kind')
                    ->label(__('Kind'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'top_up' => 'success',
                        'booking' => 'primary',
                        'refund' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'top_up' => __('Top-up'),
                        'booking' => __('Booking'),
                        'refund' => __('Refund'),
                        'adjustment' => __('Adjustment'),
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('SAR')
                    ->color(fn ($record) => $record->amount >= 0 ? 'success' : 'danger'),
                TextColumn::make('note')->label(__('Note'))->limit(60)->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('kind')->options([
                    'top_up' => __('Top-up'),
                    'booking' => __('Booking'),
                    'refund' => __('Refund'),
                    'adjustment' => __('Adjustment'),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->label(__('New transaction')),
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
