<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleModelEntry extends Model
{
    protected $fillable = [
        'vehicle_brand_id',
        'vehicle_category_id',
        'name',
        'name_ar',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    /**
     * Size band — Small, Medium or Large.
     *
     * Attached to the model rather than the customer's vehicle, so it is known
     * as soon as a car is picked. Nullable: a newly added model is
     * unclassified rather than silently treated as the smallest.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }
}
