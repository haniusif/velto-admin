<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'kind',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public const KIND_TOP_UP = 'top_up';
    public const KIND_BOOKING = 'booking';
    public const KIND_REFUND = 'refund';
    public const KIND_ADJUSTMENT = 'adjustment';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function booted(): void
    {
        static::created(function (self $tx): void {
            $tx->customer?->increment('wallet_balance', (float) $tx->amount);
        });

        static::deleted(function (self $tx): void {
            $tx->customer?->decrement('wallet_balance', (float) $tx->amount);
        });
    }
}
