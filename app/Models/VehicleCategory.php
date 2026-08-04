<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The vehicle models in this size band.
     *
     * Lets the category screen answer "what counts as Large?", which is the
     * question someone actually has when reviewing or repricing a band.
     */
    public function models(): HasMany
    {
        return $this->hasMany(VehicleModelEntry::class, 'vehicle_category_id');
    }
}
