<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerShift extends Model
{
    protected $fillable = ['worker_id', 'day_of_week', 'start_time', 'end_time'];

    protected $casts = ['day_of_week' => 'integer'];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
