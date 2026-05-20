<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'national_id',
        'city',
        'status',
        'hire_date',
        'rating',
        'notes',
        'avatar_url',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'rating' => 'decimal:2',
    ];
}
