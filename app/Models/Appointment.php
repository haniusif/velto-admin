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
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses that count as "upcoming" (still actionable). */
    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_ON_THE_WAY,
    ];

    protected $fillable = [
        'customer_id',
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
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'add_ons' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'base_price' => 'decimal:2',
        'addons_total' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
}
