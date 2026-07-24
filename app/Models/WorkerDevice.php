<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A worker's registered push device (one row per install). The dispatch push
 * fan-out reads these tokens; stale ones are pruned when FCM reports them
 * unregistered.
 */
class WorkerDevice extends Model
{
    protected $fillable = [
        'worker_id',
        'fcm_token',
        'platform',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
