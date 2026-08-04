<?php

namespace App\Filament\Resources\VehicleCategories\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * The vehicle models in this size band.
 *
 * Answers "what counts as Large?" from the category screen itself, which is
 * the question someone has when reviewing a classification or deciding what a
 * band should cost. Without it the only way to see a band's contents was to
 * open all 42 brands one at a time.
 *
 * Read-only apart from removing a model from the band: models belong to a
 * brand, so creating one here would leave it brand-less. Reclassifying is done
 * from the brand's own models list, or in bulk from here.
 */
class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Models');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('brand.name')
                    ->label(__('Brand'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->placeholder('—')
                    ->searchable(),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('vehicle_brand_id')
                    ->label(__('Brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder(__('All')),
            ])
            // Detaching clears the band rather than deleting the model — the
            // car still exists, it is just unclassified again.
            ->recordActions([DetachAction::make()->label(__('Remove from category'))])
            ->toolbarActions([BulkActionGroup::make([DetachBulkAction::make()])])
            ->defaultSort('vehicle_brand_id');
    }
}
