<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Worker extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'national_id',
        'city',
        'status',
        'preferred_language',
        'last_login_at',
        'hire_date',
        'rating',
        'notes',
        'avatar_url',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'rating' => 'decimal:2',
        'last_login_at' => 'datetime',
    ];

    /** Jobs (appointments) assigned to this worker. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function workerNotifications(): HasMany
    {
        return $this->hasMany(WorkerNotification::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
