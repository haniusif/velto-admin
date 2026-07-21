<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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

    // --- availability, skills & zones ------------------------------------

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'worker_skills');
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'worker_zones');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(WorkerShift::class);
    }

    public function timeOff(): HasMany
    {
        return $this->hasMany(WorkerTimeOff::class);
    }

    /**
     * On shift at the given moment. A worker with NO shifts defined is treated
     * as always available (unconstrained) so the gate is opt-in per worker.
     */
    public function isOnShift(?Carbon $at = null): bool
    {
        $at ??= now();
        $shifts = $this->relationLoaded('shifts') ? $this->shifts : $this->shifts()->get();
        if ($shifts->isEmpty()) {
            return true;
        }

        $time = $at->format('H:i:s');

        return $shifts
            ->where('day_of_week', $at->dayOfWeek)
            ->contains(fn (WorkerShift $s): bool => $time >= substr((string) $s->start_time, 0, 8)
                && $time <= substr((string) $s->end_time, 0, 8));
    }

    public function isOnLeave(?Carbon $on = null): bool
    {
        $date = ($on ?? now())->toDateString();
        $off = $this->relationLoaded('timeOff') ? $this->timeOff : $this->timeOff()->get();

        return $off->contains(fn (WorkerTimeOff $t): bool => $date >= $t->starts_on->toDateString()
            && $date <= $t->ends_on->toDateString());
    }

    /** True if the job needs no skill, or this worker has it. */
    public function hasSkill(?int $skillId): bool
    {
        if ($skillId === null) {
            return true;
        }
        $ids = $this->relationLoaded('skills') ? $this->skills->pluck('id') : $this->skills()->pluck('skills.id');

        return $ids->contains($skillId);
    }

    /** True if the job has no zone, the worker declares no zones, or covers it. */
    public function coversZone(?int $zoneId): bool
    {
        if ($zoneId === null) {
            return true;
        }
        $ids = $this->relationLoaded('zones') ? $this->zones->pluck('id') : $this->zones()->pluck('zones.id');

        return $ids->isEmpty() || $ids->contains($zoneId);
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
