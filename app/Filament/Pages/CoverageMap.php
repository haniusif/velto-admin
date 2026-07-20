<?php

namespace App\Filament\Pages;

use App\Models\District;
use BackedEnum;
use Filament\Pages\Page;

class CoverageMap extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.coverage-map';

    public static function getNavigationLabel(): string
    {
        return 'خريطة التغطية';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Locations');
    }

    public function getTitle(): string
    {
        return 'خريطة مناطق التغطية';
    }

    /** All Riyadh districts as lightweight features for the Google map. */
    public function districtsForMap(): array
    {
        return District::query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_covered', 'geometry'])
            ->map(fn (District $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'is_covered' => (bool) $d->is_covered,
                'geometry' => $d->geometry,
            ])
            ->all();
    }

    public function coveredCount(): int
    {
        return District::where('is_covered', true)->count();
    }

    public function googleMapsKey(): string
    {
        return (string) (config('services.google_maps.key')
            ?: 'AIzaSyCMyabfcrVJcrhMaWi92zJf5hQwfyeYqdk');
    }

    /** Livewire action fired from the map/list: flip a district's coverage. */
    public function toggleDistrict(int $id): array
    {
        $district = District::findOrFail($id);
        $district->is_covered = ! $district->is_covered;
        $district->save();

        return [
            'id' => $district->id,
            'is_covered' => $district->is_covered,
        ];
    }

    /** Bulk action: cover or clear every district at once. */
    public function setAllCovered(bool $covered): int
    {
        return District::query()->update(['is_covered' => $covered]);
    }
}
