<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleColor extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'name_ar',
        'hex',
        'is_light_swatch',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_light_swatch' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
