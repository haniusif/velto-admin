<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Worker duty state — online/offline toggle and a heartbeat that keeps the
 * worker "present" (and, from Phase 2, carries live location for the Nearest
 * strategy). Eligibility can require online via the require_online setting.
 */
class WorkerDutyController extends Controller
{
    /** POST /api/v1/worker/duty { online: bool } */
    public function setDuty(Request $request): JsonResponse
    {
        $data = $request->validate(['online' => ['required', 'boolean']]);

        $worker = $request->user();
        $worker->update([
            'is_online' => $data['online'],
            'last_seen_at' => now(),
        ]);

        return response()->json(['data' => $this->state($worker)]);
    }

    /** POST /api/v1/worker/heartbeat { lat?, lng? } — call periodically while online. */
    public function heartbeat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $worker = $request->user();
        $worker->update([
            'last_seen_at' => now(),
            'last_lat' => $data['lat'] ?? $worker->last_lat,
            'last_lng' => $data['lng'] ?? $worker->last_lng,
        ]);

        return response()->json(['data' => $this->state($worker)]);
    }

    private function state($worker): array
    {
        return [
            'is_online' => (bool) $worker->is_online,
            'last_seen_at' => $worker->last_seen_at?->toIso8601String(),
            'open_jobs' => $worker->openJobsCount(),
            'jobs_today' => $worker->jobsTodayCount(),
        ];
    }
}
