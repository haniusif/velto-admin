<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedAddress extends Model
{
    protected $fillable = [
        'customer_id', 'label', 'subtitle', 'lat', 'lng', 'is_covered', 'icon_key',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_covered' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
