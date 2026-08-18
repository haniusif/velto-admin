<?php

namespace App\Models;

use App\Support\BookingTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A promotional discount applied to a booking's total.
 */
class PromoCode extends Model
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    /** Why a code cannot be used — returned to the app so it can localize. */
    public const REASON_NOT_FOUND = 'promo_not_found';
    public const REASON_EXPIRED = 'promo_expired';
    public const REASON_EXHAUSTED = 'promo_exhausted';
    public const REASON_ALREADY_USED = 'promo_already_used';
    public const REASON_MIN_TOTAL = 'promo_min_total';

    protected $fillable = [
        'code', 'description', 'description_ar',
        'type', 'value', 'max_discount', 'min_order_total',
        'usage_limit', 'per_customer_limit', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_total' => 'decimal:2',
        'usage_limit' => 'integer',
        'per_customer_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    /** Codes are stored and matched uppercase, so case never matters to a customer. */
    public static function findByCode(?string $code): ?self
    {
        $code = trim((string) $code);

        return $code === '' ? null : static::whereRaw('UPPER(code) = ?', [mb_strtoupper($code)])->first();
    }

    public function withinWindow(): bool
    {
        // The window is typed in the admin as Riyadh wall clock but stored
        // naively, so comparing the UTC-cast value against now() ran every
        // code three hours late at both ends: it went live three hours after
        // its start and stayed redeemable three hours past its expiry.
        return $this->is_active
            && ($this->starts_at === null || BookingTime::instant($this->starts_at)->isPast())
            && ($this->expires_at === null || BookingTime::instant($this->expires_at)->isFuture());
    }

    /**
     * What this code takes off a given subtotal. Never more than the subtotal
     * itself — a discount must not turn into credit.
     */
    public function discountFor(float $subtotal): float
    {
        $raw = $this->type === self::TYPE_PERCENT
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $raw = min($raw, (float) $this->max_discount);
        }

        return round(min($raw, $subtotal), 2);
    }

    /**
     * Why this customer cannot use the code on this subtotal, or null if they
     * can. Returns a reason constant rather than a message so the app can say
     * it in the customer's language.
     */
    public function rejectionReason(int $customerId, float $subtotal): ?string
    {
        if (! $this->withinWindow()) {
            return self::REASON_EXPIRED;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return self::REASON_EXHAUSTED;
        }

        if ($subtotal < (float) $this->min_order_total) {
            return self::REASON_MIN_TOTAL;
        }

        $mine = $this->redemptions()->where('customer_id', $customerId)->count();
        if ($this->per_customer_limit > 0 && $mine >= $this->per_customer_limit) {
            return self::REASON_ALREADY_USED;
        }

        return null;
    }
}
