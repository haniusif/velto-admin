<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageAddOn extends Model
{
    protected $fillable = [
        'wash_package_id',
        'name',
        'name_ar',
        'extra_price',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function washPackage(): BelongsTo
    {
        return $this->belongsTo(WashPackage::class);
    }
}
