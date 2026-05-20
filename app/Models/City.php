<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class City extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'country',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function zones(): HasManyThrough
    {
        return $this->hasManyThrough(Zone::class, Area::class);
    }
}
