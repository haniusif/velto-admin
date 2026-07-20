<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'city',
        'name',
        'name_ar',
        'slug',
        'geometry',
        'is_covered',
    ];

    protected $casts = [
        'geometry' => 'array',
        'is_covered' => 'boolean',
    ];

    public function scopeCovered($query)
    {
        return $query->where('is_covered', true);
    }
}
