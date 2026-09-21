<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One request a customer raised from the Help Center. The team answers it
 * once from the panel (`admin_reply`); a back-and-forth thread is not modelled
 * because anything longer than that moves to WhatsApp anyway.
 */
class SupportTicket extends Model
{
    public const TYPE_COMPLAINT = 'complaint';
    public const TYPE_SUGGESTION = 'suggestion';
    public const TYPE_INQUIRY = 'inquiry';
    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_COMPLAINT,
        self::TYPE_SUGGESTION,
        self::TYPE_INQUIRY,
        self::TYPE_OTHER,
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'customer_id',
        'appointment_id',
        'type',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_COMPLAINT => __('Complaint'),
            self::TYPE_SUGGESTION => __('Suggestion'),
            self::TYPE_INQUIRY => __('Inquiry'),
            self::TYPE_OTHER => __('Other'),
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => __('Open'),
            self::STATUS_IN_PROGRESS => __('In progress'),
            self::STATUS_RESOLVED => __('Resolved'),
            self::STATUS_CLOSED => __('Closed'),
        ];
    }
}
