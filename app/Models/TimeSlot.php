<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'capacity',
        'booked_count',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'capacity' => 'integer',
        'booked_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected function isFull(): Attribute
    {
        return Attribute::get(fn (): bool => $this->booked_count >= $this->capacity);
    }
}
