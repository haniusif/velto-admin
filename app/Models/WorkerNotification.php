<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerNotification extends Model
{
    protected $fillable = [
        'worker_id',
        'kind',
        'title',
        'title_ar',
        'body',
        'body_ar',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public const KIND_ASSIGNED = 'assigned';
    public const KIND_OFFERED = 'offered';
    public const KIND_OFFER_EXPIRED = 'offer_expired';
    public const KIND_REASSIGNED_AWAY = 'reassigned_away';
    public const KIND_RESCHEDULED = 'rescheduled';
    public const KIND_CANCELLED = 'cancelled';
    public const KIND_REMINDER = 'reminder';

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
