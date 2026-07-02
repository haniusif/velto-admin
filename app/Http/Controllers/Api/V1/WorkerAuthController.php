<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkerResource;
use App\Models\Worker;
use App\Services\JawalySMSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkerAuthController extends Controller
{
    public function __construct(private readonly JawalySMSService $sms) {}

    /** POST /api/v1/worker/auth/request-otp */
    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:32'],
        ]);

        $phone = $this->normalizePhone($data['phone']);

        // Workers are provisioned by the admin — don't leak which numbers exist,
        // but also don't bother sending SMS to a non-worker. Treat as success either way.
        $worker = Worker::where('phone', $phone)->where('status', 'active')->first();
        if (! $worker) {
            Log::info('Worker OTP requested for unknown/inactive phone', ['phone' => $phone]);

            return response()->json([
                'data' => ['phone' => $phone, 'expires_in' => 600, 'dev_code' => null],
            ]);
        }

        // Throttle: max 1 OTP request per phone per 60 seconds.
        $recent = DB::table('phone_otps')
            ->where('phone', $phone)
            ->where('created_at', '>=', now()->subSeconds(60))
            ->orderByDesc('id')
            ->first();

        if ($recent) {
            $elapsed = now()->getTimestamp() - \Carbon\Carbon::parse($recent->created_at)->getTimestamp();
            $retryAfter = max(1, 60 - $elapsed);

            return response()->json([
                'message' => 'Please wait before requesting another code.',
                'errors' => ['phone' => ["Try again in {$retryAfter}s."]],
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        $smsConfigured = $this->sms->isConfigured();

        // Real SMS path → random code. Offline dev (no creds) → backdoor 1111.
        $code = $smsConfigured ? (string) random_int(1000, 9999) : '1111';

        DB::table('phone_otps')->insert([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($smsConfigured) {
            $result = $this->sms->sendOtp($phone, $code);
            if (! ($result['success'] ?? false)) {
                Log::warning('Worker OTP SMS send failed', ['phone' => $phone, 'result' => $result]);

                return response()->json([
                    'message' => 'Could not send SMS at this time.',
                    'errors' => ['phone' => ['SMS provider error.']],
                ], 502);
            }
        } else {
            Log::info('Worker OTP (local backdoor — SMS not configured)', ['phone' => $phone, 'code' => $code]);
        }

        return response()->json([
            'data' => [
                'phone' => $phone,
                'expires_in' => 600,
                'dev_code' => $smsConfigured ? null : $code,
            ],
        ]);
    }

    /** POST /api/v1/worker/auth/verify-otp */
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:4'],
        ]);

        $phone = $this->normalizePhone($data['phone']);

        $worker = Worker::where('phone', $phone)->where('status', 'active')->first();
        if (! $worker) {
            return response()->json([
                'message' => 'No active worker account for this number.',
                'errors' => ['phone' => ['No active worker account for this number.']],
            ], 403);
        }

        $otp = DB::table('phone_otps')
            ->where('phone', $phone)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (! $otp || $otp->code !== $data['code']) {
            return response()->json([
                'message' => 'Invalid or expired code.',
                'errors' => ['code' => ['Invalid or expired code.']],
            ], 422);
        }

        DB::table('phone_otps')->where('id', $otp->id)->update([
            'used_at' => now(),
            'updated_at' => now(),
        ]);

        $worker->forceFill(['last_login_at' => now()])->save();

        $token = $worker->createToken('worker-mobile')->plainTextToken;

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

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($phone, '+')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '966')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '05')) {
            return '+966'.substr($digits, 1);
        }

        return '+'.$digits;
    }
}
