<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A prepaid multi-visit plan a customer has bought: N visits of one wash
 * package, locked to one vehicle, valid until expires_at. Unused visits are
 * forfeited at expiry.
 */
class CustomerPackage extends Model
{
    public const STATUS_PENDING = 'pending';     // card payment not captured yet
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_id',
        'wash_package_id',
        'vehicle_id',
        'visits_total',
        'visits_used',
        'price_paid',
        'payment_method',
        'payment_status',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'visits_total' => 'integer',
        'visits_used' => 'integer',
        'price_paid' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function washPackage(): BelongsTo
    {
        return $this->belongsTo(WashPackage::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function visitsRemaining(): int
    {
        return max(0, $this->visits_total - $this->visits_used);
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Whether a visit can be spent right now. Expiry is evaluated here rather
     * than relying on the stored status, so a plan that lapsed since the last
     * write is refused even before any sweep has run.
     */
    public function isUsable(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->payment_status === 'paid'
            && ! $this->hasExpired()
            && $this->visitsRemaining() > 0;
    }

    /** The status to report, accounting for an expiry that hasn't been swept yet. */
    public function effectiveStatus(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->hasExpired()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status;
    }

    /**
     * Start the validity window and make the plan spendable. Called on purchase
     * for wallet payments and on capture for card ones — a card plan must not
     * start counting down while the customer is still on the hosted page.
     */
    public function activate(): void
    {
        $days = (int) ($this->washPackage?->validity_days ?: 0);

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'starts_at' => now(),
            'expires_at' => $days > 0 ? now()->addDays($days) : null,
        ]);
    }
}
