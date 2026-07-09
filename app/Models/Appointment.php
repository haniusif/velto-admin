<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_ON_THE_WAY = 'on_the_way';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses that count as "upcoming" (still actionable). */
    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_ON_THE_WAY,
        self::STATUS_ARRIVED,
        self::STATUS_IN_PROGRESS,
    ];

    protected $fillable = [
        'customer_id',
        'worker_id',
        'vehicle_id',
        'wash_package_id',
        'time_slot_id',
        'wallet_transaction_id',
        'status',
        'scheduled_at',
        'address_label',
        'latitude',
        'longitude',
        'area_id',
        'zone_id',
        'service_name',
        'service_name_ar',
        'vehicle_label',
        'add_ons',
        'base_price',
        'addons_total',
        'total_price',
        'payment_method',
        'payment_status',
        'notes',
        'cancelled_at',
        'completed_at',
        'accepted_at',
        'started_at',
        'arrived_at',
        'work_started_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'arrived_at' => 'datetime',
        'work_started_at' => 'datetime',
        'add_ons' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'base_price' => 'decimal:2',
        'addons_total' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Notify a worker when they are assigned (or re-assigned) to a job.
        static::saved(function (Appointment $appointment): void {
            if (! $appointment->wasChanged('worker_id') || $appointment->worker_id === null) {
                return;
            }

            $when = $appointment->scheduled_at?->format('Y-m-d H:i');
            $serviceAr = $appointment->service_name_ar ?: $appointment->service_name;

            WorkerNotification::create([
                'worker_id' => $appointment->worker_id,
                'kind' => WorkerNotification::KIND_ASSIGNED,
                'title' => 'New job assigned',
                'title_ar' => 'تم إسناد مهمة جديدة',
                'body' => trim("{$appointment->service_name} — {$when}"),
                'body_ar' => trim("{$serviceAr} — {$when}"),
                'data' => ['appointment_id' => $appointment->id],
            ]);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function washPackage(): BelongsTo
    {
        return $this->belongsTo(WashPackage::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    protected function isUpcoming(): Attribute
    {
        return Attribute::get(
            fn (): bool => in_array($this->status, self::ACTIVE_STATUSES, true)
        );
    }

    /** Can be cancelled/rescheduled only while still active and in the future. */
    public function isActionable(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true)
            && $this->scheduled_at !== null
            && $this->scheduled_at->isFuture();
    }

    /**
     * A card booking left pending because payment was never completed (the
     * customer abandoned the hosted page). Its slot is still held, so payment
     * can be retried while the slot is in the future.
     */
    public function canPay(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->payment_method !== 'wallet'
            && $this->payment_status === 'pending'
            && $this->scheduled_at !== null
            && $this->scheduled_at->isFuture();
    }

    // --- worker job lifecycle -------------------------------------------

    /** Worker may acknowledge an assigned, still-active job once. */
    public function workerCanAccept(): bool
    {
        return $this->worker_id !== null
            && $this->accepted_at === null
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    /** Worker may set out once the job is accepted and not yet started. */
    public function workerCanStart(): bool
    {
        return $this->worker_id !== null
            && $this->accepted_at !== null
            && $this->started_at === null
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    /** Worker may mark arrival once on the way. */
    public function workerCanArrive(): bool
    {
        return $this->worker_id !== null
            && $this->arrived_at === null
            && $this->status === self::STATUS_ON_THE_WAY;
    }

    /** Worker may begin the work (starts the timer) once arrived on-site. */
    public function workerCanStartWork(): bool
    {
        return $this->worker_id !== null
            && $this->work_started_at === null
            && $this->status === self::STATUS_ARRIVED;
    }

    /** Worker may complete a job once the work is in progress. */
    public function workerCanComplete(): bool
    {
        return $this->worker_id !== null
            && $this->status === self::STATUS_IN_PROGRESS;
    }
}
