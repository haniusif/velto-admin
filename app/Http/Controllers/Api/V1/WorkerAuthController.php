<?php

namespace App\Http\Controllers\Api\V1;

use App\Rules\SaudiMobile;
use App\Support\SaudiPhone;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkerResource;
use App\Models\Worker;
use App\Services\Auth\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkerAuthController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    /** POST /api/v1/worker/auth/request-otp */
    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32', new SaudiMobile],
        ]);

        $phone = SaudiPhone::normalize($data['phone']);

        // Workers are provisioned by the admin — don't leak which numbers exist,
        // but also don't bother sending SMS to a non-worker. Treat as success either way.
        $worker = Worker::where('phone', $phone)->where('status', 'active')->first();
        if (! $worker) {
            Log::info('Worker OTP requested for unknown/inactive phone', ['phone' => $phone]);

            return response()->json([
                'data' => ['phone' => $phone, 'expires_in' => OtpService::LIFETIME_MINUTES * 60, 'dev_code' => null],
            ]);
        }

        $devCode = $this->otp->issue($phone, 'worker');

        return response()->json([
            'data' => ['phone' => $phone, 'expires_in' => OtpService::LIFETIME_MINUTES * 60, 'dev_code' => $devCode],
        ]);
    }

    /** POST /api/v1/worker/auth/verify-otp */
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32', new SaudiMobile],
            'code' => ['required', 'string', 'digits:4'],
        ]);

        $phone = SaudiPhone::normalize($data['phone']);

        $worker = Worker::where('phone', $phone)->where('status', 'active')->first();
        if (! $worker) {
            return response()->json([
                'message' => 'No active worker account for this number.',
                'errors' => ['phone' => ['No active worker account for this number.']],
            ], 403);
        }

        if (! $this->otp->verify($phone, $data['code'])) {
            return response()->json([
                'message' => 'Invalid or expired code.',
                'errors' => ['code' => ['Invalid or expired code.']],
                'code' => 'invalid_code',
            ], 422);
        }

        $worker->forceFill(['last_login_at' => now()])->save();

        $token = $worker->createToken('worker-mobile', ['*'], now()->addDays(180))->plainTextToken;
        $stale = $worker->tokens()->orderByDesc('id')->skip(5)->take(100)->pluck('id');
        if ($stale->isNotEmpty()) {
            $worker->tokens()->whereIn('id', $stale)->delete();
        }

        return response()->json([
            'data' => [
                'token' => $token,
                'worker' => new WorkerResource($worker),
            ],
        ]);
    }

    /** GET /api/v1/worker/auth/me */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => new WorkerResource($request->user())]);
    }

    /** POST /api/v1/worker/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

}
