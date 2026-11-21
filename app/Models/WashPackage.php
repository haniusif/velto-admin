<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WashPackage extends Model
{
    /**
     * "Most requested" is a single designation: when one package is marked
     * featured, every other package is unmarked, so only one ever carries the
     * badge. The sweep uses a query-builder update, which fires no model
     * events, so it cannot recurse.
     */
    protected static function booted(): void
    {
        static::saved(function (WashPackage $package): void {
            if ($package->is_featured) {
                static::query()
                    ->whereKeyNot($package->getKey())
                    ->where('is_featured', true)
                    ->update(['is_featured' => false]);
            }
        });
    }

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'type',
        'price',
        'original_price',
        'duration_minutes',
        'required_skill_id',
        'dispatch_strategy',
        'visits_count',
        'validity_days',
        'image_path',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
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
