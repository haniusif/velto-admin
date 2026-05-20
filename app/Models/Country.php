<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'code',
        'dial',
        'flag',
        'name',
        'name_ar',
        'phone_length',
        'sort_order',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'phone_length' => 'integer',
        'sort_order' => 'integer',
    ];
}
