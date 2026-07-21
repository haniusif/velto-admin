<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One immutable row per dispatch decision — the audit trail and the analytics /
 * future-ML training spine. Append-only (no updated_at).
 */
class DispatchEvent extends Model
{
    public const UPDATED_AT = null;

    public const TYPE_SCHEDULED = 'scheduled';
    public const TYPE_WAITING = 'waiting';
    public const TYPE_ASSIGNED = 'assigned';
    public const TYPE_OFFERED = 'offered';
    public const TYPE_ACCEPTED = 'accepted';
    public const TYPE_REJECTED = 'rejected';
    public const TYPE_EXPIRED = 'expired';
    public const TYPE_REASSIGNED = 'reassigned';

    protected $fillable = ['appointment_id', 'worker_id', 'type', 'reason', 'actor', 'meta', 'created_at'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public static function record(
        int $appointmentId, string $type, ?int $workerId = null,
        string $actor = 'engine', ?string $reason = null, array $meta = []
    ): void {
        static::create([
            'appointment_id' => $appointmentId,
            'worker_id' => $workerId,
            'type' => $type,
            'actor' => $actor,
            'reason' => $reason,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }
}
