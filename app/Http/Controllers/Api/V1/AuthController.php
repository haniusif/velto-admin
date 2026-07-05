<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\Customer;
use App\Services\JawalySMSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(private readonly JawalySMSService $sms) {}

    /** POST /api/v1/auth/request-otp */
    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:32'],
        ]);

        $phone = $this->normalizePhone($data['phone']);

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

        // Real SMS path → code derived from current minute+hour (mmHH). Offline dev (no creds) → backdoor 1111.
        $code = $smsConfigured
            ? date('iH')
            : '1111';

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
                Log::warning('OTP SMS send failed', ['phone' => $phone, 'result' => $result]);

                return response()->json([
                    'message' => 'Could not send SMS at this time.',
                    'errors' => ['phone' => ['SMS provider error.']],
                ], 502);
            }
        } else {
            Log::info('OTP (local backdoor — SMS not configured)', ['phone' => $phone, 'code' => $code]);
        }

        return response()->json([
            'data' => [
                'phone' => $phone,
                'expires_in' => 600,
                // Only echo back the code when SMS is offline (offline dev).
                'dev_code' => $smsConfigured ? null : $code,
            ],
        ]);
    }

    /** POST /api/v1/auth/verify-otp */
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:4'],
        ]);

        $phone = $this->normalizePhone($data['phone']);

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

        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'Customer '.Str::after($phone, '+966'),
                'status' => 'active',
                'preferred_language' => app()->getLocale() === 'ar' ? 'ar' : 'en',
                'profile_completed' => false,
                'joined_at' => now(),
            ],
        );

        $token = $customer->createToken('mobile')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'customer' => new CustomerResource($customer),
            ],
        ]);
    }

    /** GET /api/v1/auth/me */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new CustomerResource($request->user()),
        ]);
    }

    /** POST /api/v1/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    /** PATCH /api/v1/auth/profile */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gender' => ['sometimes', 'nullable', 'in:male,female'],
            'preferred_language' => ['sometimes', 'string', 'in:ar,en'],
        ]);

        $customer->fill($data);

        if (! $customer->profile_completed && filled($customer->name) && filled($customer->city)) {
            $customer->profile_completed = true;
        }

        $customer->save();

        return response()->json([
            'data' => new CustomerResource($customer->fresh()),
        ]);
    }

    /** POST /api/v1/auth/avatar (multipart, field: avatar) */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:4096'],
        ]);

        /** @var Customer $customer */
        $customer = $request->user();

        if ($customer->avatar_url && Storage::disk('public')->exists($customer->avatar_url)) {
            Storage::disk('public')->delete($customer->avatar_url);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $customer->avatar_url = $path;
        $customer->save();

        return response()->json([
            'data' => new CustomerResource($customer->fresh()),
        ]);
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
