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

        // Only while a job is actually under way — not merely accepted or
        // scheduled. The customer can only ever see a position during these
        // two statuses, so anything stored outside them is staff location data
        // with no purpose. The app already starts sharing at "set off"; this
        // makes the server the thing that enforces it.
        $isEnRoute = $worker->appointments()
            ->whereIn('status', [
                Appointment::STATUS_ON_THE_WAY,
                Appointment::STATUS_ARRIVED,
            ])
            ->exists();

        if (! $isEnRoute) {
            // Not an error the app should retry — it should stop sending.
            return response()->json([
                'data' => ['accepted' => false],
                'message' => 'Not en route to a job; location is not being collected.',
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
