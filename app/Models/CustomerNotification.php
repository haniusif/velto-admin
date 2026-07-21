<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
{
    protected $fillable = [
        'customer_id',
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

    public const KIND_WELCOME = 'welcome';
    public const KIND_BOOKING = 'booking';
    public const KIND_WORKER_ASSIGNED = 'worker_assigned';
    public const KIND_WORKER_CHANGED = 'worker_changed';
    public const KIND_ON_THE_WAY = 'on_the_way';
    public const KIND_ARRIVED = 'arrived';
    public const KIND_COMPLETED = 'completed';
    public const KIND_PROMO = 'promo';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
