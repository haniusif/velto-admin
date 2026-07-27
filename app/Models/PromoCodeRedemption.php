<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One use of a promo code, and what it cost. */
class PromoCodeRedemption extends Model
{
    protected $fillable = ['promo_code_id', 'customer_id', 'appointment_id', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
