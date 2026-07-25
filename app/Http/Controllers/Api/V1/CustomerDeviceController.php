<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Customer push-device registration. The app posts its FCM token on login and
 * whenever the token rotates; it deletes it on logout. Tokens are unique per
 * install, so a token re-registering under a new customer moves to that customer.
 */
class CustomerDeviceController extends Controller
{
    /** POST /api/v1/me/devices { fcm_token, platform? } */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        $customer = $request->user();

        $device = CustomerDevice::updateOrCreate(
            ['fcm_token' => $data['fcm_token']],
            [
                'customer_id' => $customer->id,
                'platform' => $data['platform'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['data' => ['id' => $device->id]], 201);
    }

    /** DELETE /api/v1/me/devices { fcm_token } — call on logout / token invalidation. */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $request->user()->devices()
            ->where('fcm_token', $data['fcm_token'])
            ->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
