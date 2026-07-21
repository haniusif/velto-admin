<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentOffer extends Model
{
    public const STATUS_OFFERED = 'offered';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ASSIGNED = 'assigned'; // direct-mode assignment (no consent step)

    protected $fillable = [
        'appointment_id',
        'worker_id',
        'status',
        'score',
        'factors',
        'reason',
        'attempt',
        'offered_at',
        'expires_at',
        'responded_at',
    ];

    protected $casts = [
        'factors' => 'array',
        'score' => 'float',
        'attempt' => 'integer',
        'offered_at' => 'datetime',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_OFFERED;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_OFFERED);
    }
}
