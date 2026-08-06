<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'brand',
        'model',
        'color',
        'plate',
        'photo_url',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The size band this car falls into, or null if it can't be placed.
     *
     * A customer's vehicle stores brand and model as free text — it is not a
     * foreign key into the catalogue — so the band is resolved by matching
     * both against `vehicle_model_entries`. Both halves must match: model
     * names repeat across brands (Lexus LX and RX both collide), so matching
     * on the model alone would price some cars off the wrong brand's entry.
     *
     * Returns null when the car was typed free-hand ("ES350"), or when its
     * model exists but has no band yet. Callers must have a fallback — never
     * treat null as the cheapest band.
     */
    public function sizeCategory(): ?VehicleCategory
    {
        if (array_key_exists('sizeCategory', $this->relations)) {
            return $this->relations['sizeCategory'];
        }

        $brand = mb_strtolower(trim((string) $this->brand));
        $model = mb_strtolower(trim((string) $this->model));

        $category = null;

        if ($brand !== '' && $model !== '') {
            $entry = VehicleModelEntry::query()
                ->whereNotNull('vehicle_category_id')
                ->where(fn ($q) => $q
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$model])
                    ->orWhereRaw('LOWER(TRIM(name_ar)) = ?', [$model]))
                ->whereHas('brand', fn ($q) => $q
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$brand])
                    ->orWhereRaw('LOWER(TRIM(name_ar)) = ?', [$brand]))
                ->with('category')
                ->first();

            $category = $entry?->category;
        }

        $this->relations['sizeCategory'] = $category;

        return $category;
    }
}
