<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WashPackage extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'type',
        'price',
        'duration_minutes',
        'visits_count',
        'validity_days',
        'image_path',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'visits_count' => 'integer',
        'validity_days' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function addOns(): HasMany
    {
        return $this->hasMany(PackageAddOn::class);
    }
}
