<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\WorkerLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The specialist's live position while they are on a job.
 *
 * Accepted only while they actually have an active job: outside one there is
 * nobody waiting to see it, and storing it anyway would be tracking staff
 * rather than serving a booking.
 */
class WorkerLocationController extends Controller
{
    /** POST /api/v1/worker/location */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
        ]);

        $worker = $request->user();

        $hasActiveJob = $worker->appointments()
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->exists();

        if (! $hasActiveJob) {
            // Not an error the app should retry — it should stop sending.
            return response()->json([
                'data' => ['accepted' => false],
                'message' => 'No active job; location is not being collected.',
                'code' => 'no_active_job',
            ], 200);
        }

        WorkerLocation::updateOrCreate(
            ['worker_id' => $worker->id],
            [
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'accuracy' => $data['accuracy'] ?? null,
                'heading' => $data['heading'] ?? null,
            ],
        );

        return response()->json(['data' => ['accepted' => true]]);
    }

    /** DELETE /api/v1/worker/location — stop sharing immediately. */
    public function destroy(Request $request): JsonResponse
    {
        WorkerLocation::where('worker_id', $request->user()->id)->delete();

        return response()->json(['data' => ['cleared' => true]]);
    }
}
