<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zone extends Model
{
    protected $fillable = [
        'area_id',
        'name',
        'name_ar',
        'color',
        'geometry',
        'is_active',
    ];

    protected $casts = [
        'geometry' => 'array',
        'is_active' => 'boolean',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
