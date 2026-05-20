<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'title_ar',
        'body',
        'body_ar',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
