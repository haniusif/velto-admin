<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer's registered push device (one row per install). Booking-status
 * notifications fan out to these tokens; stale ones are pruned when FCM reports
 * them unregistered.
 */
class CustomerDevice extends Model
{
    protected $fillable = [
        'customer_id',
        'fcm_token',
        'platform',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
