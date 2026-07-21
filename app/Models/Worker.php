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
        'max_jobs_per_day',
        'max_concurrent_jobs',
        'acceptance_rate',
        'is_online',
        'last_seen_at',
        'last_lat',
        'last_lng',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'rating' => 'decimal:2',
        'last_login_at' => 'datetime',
        'max_jobs_per_day' => 'integer',
        'max_concurrent_jobs' => 'integer',
        'acceptance_rate' => 'float',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_lat' => 'float',
        'last_lng' => 'float',
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

    public function offers(): HasMany
    {
        return $this->hasMany(AssignmentOffer::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // --- dispatch capacity ------------------------------------------------

    /** Open (assigned, not-yet-finished) jobs — the concurrent workload. */
    public function openJobsCount(): int
    {
        return $this->appointments()
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->count();
    }

    /** Jobs assigned to this worker scheduled for today (daily-cap gate). */
    public function jobsTodayCount(): int
    {
        return $this->appointments()
            ->whereDate('scheduled_at', today())
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->count();
    }

    public function isUnderConcurrentCap(): bool
    {
        return $this->openJobsCount() < max(1, (int) $this->max_concurrent_jobs);
    }

    public function isUnderDailyCap(): bool
    {
        return $this->jobsTodayCount() < max(1, (int) $this->max_jobs_per_day);
    }
}
