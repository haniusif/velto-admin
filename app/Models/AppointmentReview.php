<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer's rating of one completed job. One per appointment.
 */
class AppointmentReview extends Model
{
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'worker_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Recompute the worker's headline rating from their reviews.
     *
     * Stored rather than averaged on read because it is shown on every job
     * card and in dispatch ranking, and a review is written far less often
     * than the number is read.
     */
    public static function refreshWorkerRating(?int $workerId): void
    {
        if ($workerId === null) {
            return;
        }

        $average = static::where('worker_id', $workerId)->avg('rating');

        Worker::whereKey($workerId)->update([
            'rating' => $average === null ? null : round((float) $average, 2),
        ]);
    }
}
