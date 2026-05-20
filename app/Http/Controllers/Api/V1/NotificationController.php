<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CustomerNotificationResource;
use App\Models\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->customerNotifications()
            ->latest('id')
            ->limit(100)
            ->get();

        $unreadCount = $request->user()
            ->customerNotifications()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => [
                'unread_count' => $unreadCount,
                'items' => CustomerNotificationResource::collection($items),
            ],
        ]);
    }

    public function markRead(Request $request, CustomerNotification $notification): JsonResponse
    {
        abort_unless($notification->customer_id === $request->user()?->id, 404);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['data' => new CustomerNotificationResource($notification->fresh())]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->customerNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['data' => ['ok' => true]]);
    }
}
