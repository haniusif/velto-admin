<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerLocation extends Model
{
    /**
     * A position older than this is not shown to the customer. A stale dot on
     * a map is worse than no dot: it says "your specialist is here" when they
     * may have moved on minutes ago.
     */
    public const FRESH_SECONDS = 180;

    protected $fillable = ['worker_id', 'lat', 'lng', 'accuracy', 'heading'];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'accuracy' => 'float',
        'heading' => 'float',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function isFresh(): bool
    {
        if ($this->updated_at === null) {
            return false;
        }

        // abs(): Carbon 3 returns a SIGNED difference, so a timestamp that
        // looks like it is in the future — clock skew on the worker's device,
        // or a timezone mishap — would otherwise sail through this check and
        // a stale position would be shown as live.
        return abs($this->updated_at->diffInSeconds(now())) <= self::FRESH_SECONDS;
    }
}
