<?php

namespace App\Http\Controllers\Api\V1;

use App\Rules\SaudiMobile;
use App\Services\Account\DeleteCustomerAccount;
use App\Support\SaudiPhone;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Services\Auth\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    /** POST /api/v1/auth/request-otp */
    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32', new SaudiMobile],
        ]);

        $phone = SaudiPhone::normalize($data['phone']);

        // A blocked number learns nothing here: same response, no SMS.
        if (Customer::where('phone', $phone)->where('status', 'blocked')->exists()) {
            return response()->json(['data' => ['phone' => $phone, 'expires_in' => OtpService::LIFETIME_MINUTES * 60, 'dev_code' => null]]);
        }

        $devCode = $this->otp->issue($phone, 'customer');

        return response()->json([
            'data' => [
                'phone' => $phone,
                'expires_in' => OtpService::LIFETIME_MINUTES * 60,
                // Only echoed when SMS is offline (local development).
                'dev_code' => $devCode,
            ],
        ]);
    }

    /** POST /api/v1/auth/verify-otp */
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32', new SaudiMobile],
            'code' => ['required', 'string', 'digits:4'],
        ]);

        $phone = SaudiPhone::normalize($data['phone']);

        if (! $this->otp->verify($phone, $data['code'])) {
            return response()->json([
                'message' => 'Invalid or expired code.',
                'errors' => ['code' => ['Invalid or expired code.']],
                'code' => 'invalid_code',
            ], 422);
        }

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

        if ($customer->status === 'blocked') {
            return response()->json([
                'message' => 'This account has been suspended. Please contact support.',
                'code' => 'account_blocked',
            ], 403);
        }

        // One token per sign-in, named after the client so the panel can tell
        // web from app sessions; keep only the newest few so a lost phone's
        // tokens age out rather than living forever.
        $client = str_contains((string) $request->userAgent(), 'Mozilla') ? 'web' : 'mobile';
        $token = $customer->createToken($client, ['*'], now()->addDays(180))->plainTextToken;
        $stale = $customer->tokens()->orderByDesc('id')->skip(10)->take(100)->pluck('id');
        if ($stale->isNotEmpty()) {
            $customer->tokens()->whereIn('id', $stale)->delete();
        }

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

    /**
     * DELETE /api/v1/auth/account
     *
     * Deletes the signed-in customer and their personal data. Required by App
     * Store Guideline 5.1.1(v) — a deactivate-only flow is not acceptable, so
     * this is irreversible and the app confirms before calling it.
     */
    public function deleteAccount(Request $request, DeleteCustomerAccount $delete): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $delete($customer);

        return response()->json(['data' => ['deleted' => true]]);
    }

    /** PATCH /api/v1/auth/profile */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $data = $request->validate([
            // Letters (any script), spaces and the handful of name punctuation
            // marks — no digits, no markup, no control characters.
            'name' => ['sometimes', 'string', 'min:2', 'max:60', 'regex:/^[\p{L}\p{M}\s\'\-\.]+$/u'],
            'email' => [
                'sometimes',
                'nullable',
                'email:rfc',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],
            'city' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[\p{L}\p{M}\s\-]+$/u'],
            'area' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[\p{L}\p{M}\p{N}\s\-]+$/u'],
            'gender' => ['sometimes', 'nullable', 'in:male,female'],
            'preferred_language' => ['sometimes', 'string', 'in:ar,en'],
        ], [
            'name.regex' => 'The name may only contain letters.',
        ]);

        foreach (['name', 'city', 'area'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim(preg_replace('/\s+/u', ' ', $data[$field]));
            }
        }
        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = mb_strtolower(trim($data['email']));
        }

        $customer->fill($data);

        // Detect first-time registration completion so we can welcome them once.
        $justCompleted = false;
        if (! $customer->profile_completed && filled($customer->name) && filled($customer->city)) {
            $customer->profile_completed = true;
            $justCompleted = true;
        }

        $customer->save();

        if ($justCompleted) {
            $this->sendWelcomeNotification($customer);
        }

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

    /**
     * Create a one-time welcome notification for a newly registered customer.
     * Idempotent: never adds a second welcome even if profile is re-completed.
     */
    private function sendWelcomeNotification(Customer $customer): void
    {
        $customer->customerNotifications()->firstOrCreate(
            ['kind' => CustomerNotification::KIND_WELCOME],
            [
                'title' => 'Welcome to Velto 🎉',
                'title_ar' => 'أهلًا بك في فيلتو 🎉',
                'body' => "Hi {$customer->name}, your account is ready. Book your first mobile car wash today!",
                'body_ar' => "مرحبًا {$customer->name}، حسابك جاهز. احجز أول غسيل متنقل لسيارتك اليوم!",
            ],
        );
    }

}
