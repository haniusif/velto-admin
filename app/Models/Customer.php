<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'city',
        'area',
        'gender',
        'preferred_language',
        'status',
        'profile_completed',
        'notes',
        'avatar_url',
        'wallet_balance',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'profile_completed' => 'boolean',
        'wallet_balance' => 'decimal:2',
    ];

    protected $hidden = [
        'remember_token',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function customerNotifications(): HasMany
    {
        return $this->hasMany(CustomerNotification::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
