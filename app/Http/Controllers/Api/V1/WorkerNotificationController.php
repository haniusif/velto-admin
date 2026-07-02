<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkerNotificationResource;
use App\Models\WorkerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerNotificationController extends Controller
{
    /** GET /api/v1/worker/notifications */
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->workerNotifications()
            ->latest('id')
            ->limit(100)
            ->get();

        $unreadCount = $request->user()
            ->workerNotifications()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => [
                'unread_count' => $unreadCount,
                'items' => WorkerNotificationResource::collection($items),
            ],
        ]);
    }

    /** POST /api/v1/worker/notifications/{notification}/read */
    public function markRead(Request $request, WorkerNotification $notification): JsonResponse
    {
        abort_unless($notification->worker_id === $request->user()?->id, 404);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['data' => new WorkerNotificationResource($notification->fresh())]);
    }

    /** POST /api/v1/worker/notifications/read-all */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->workerNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['data' => ['ok' => true]]);
    }
}
