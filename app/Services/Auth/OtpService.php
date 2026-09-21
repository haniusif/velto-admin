<?php

namespace App\Services\Auth;

use App\Services\JawalySMSService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * One-time codes for phone sign-in, shared by the customer and worker apps.
 *
 * What this guards against, in order of how much it would cost us:
 *
 * - SMS pumping: an attacker asking for codes to thousands of numbers we pay
 *   to text. Per-phone caps (1/min, 5/hour, 10/day) and, on top, the
 *   per-IP throttle on the route.
 * - Brute force: a four-digit code has 10,000 values. A code dies after
 *   MAX_ATTEMPTS wrong guesses, and every code for that phone is voided
 *   the moment one is used, so a guess can never race a real login.
 * - Database leaks: codes are stored as an HMAC of the code, never plain,
 *   so a dump of phone_otps signs nobody in.
 */
class OtpService
{
    public const LIFETIME_MINUTES = 10;
    public const MAX_ATTEMPTS = 5;
    public const PER_PHONE_PER_HOUR = 5;
    public const PER_PHONE_PER_DAY = 10;
    public const RESEND_SECONDS = 60;

    public function __construct(private readonly JawalySMSService $sms) {}

    /**
     * Issue a code for $phone (already E.164). Returns the dev code when SMS
     * is offline, otherwise null.
     *
     * @throws ValidationException with a Retry-After hint on throttling
     */
    public function issue(string $phone, string $audience): ?string
    {
        $isTestPhone = in_array($phone, (array) config('services.otp.test_phones', []), true);

        if (! $isTestPhone) {
            $this->enforcePhoneCaps($phone);
        }

        $smsConfigured = $this->sms->isConfigured();

        $code = match (true) {
            $isTestPhone => (string) config('services.otp.test_code', '1234'),
            $smsConfigured => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            default => '1111',
        };

        // A fresh request supersedes anything outstanding: only the newest
        // code is ever valid, so an old one someone glimpsed is worthless.
        DB::table('phone_otps')->where('phone', $phone)->whereNull('used_at')->update(['used_at' => now(), 'updated_at' => now()]);

        $id = DB::table('phone_otps')->insertGetId([
            'phone' => $phone,
            'code' => $this->hash($phone, $code),
            'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($smsConfigured && ! $isTestPhone) {
            $result = $this->sms->sendOtp($phone, $code);
            if (! ($result['success'] ?? false)) {
                Log::warning('OTP SMS send failed', ['phone' => $phone, 'audience' => $audience, 'result' => $result]);
                // Don't strand a code nobody received or hold the throttle
                // against someone who never got a message.
                DB::table('phone_otps')->where('id', $id)->delete();

                throw ValidationException::withMessages(['phone' => ['SMS provider error.']])
                    ->status(502);
            }

            return null;
        }

        Log::info('OTP issued without SMS', ['phone' => $phone, 'audience' => $audience, 'test' => $isTestPhone]);

        return $smsConfigured ? null : $code;
    }

    /**
     * Check $code against the newest live code for $phone. Consumes it on
     * success; counts the failure otherwise and kills the code once it has
     * been guessed at too often.
     */
    public function verify(string $phone, string $code): bool
    {
        $otp = DB::table('phone_otps')
            ->where('phone', $phone)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (! $otp) {
            return false;
        }

        if (! hash_equals($otp->code, $this->hash($phone, $code))) {
            $attempts = (int) $otp->attempts + 1;
            $update = ['attempts' => $attempts, 'updated_at' => now()];
            if ($attempts >= self::MAX_ATTEMPTS) {
                $update['used_at'] = now(); // voided, not consumed — a new code is needed
                Log::notice('OTP locked after repeated wrong guesses', ['phone' => $phone, 'ip' => request()->ip()]);
            }
            DB::table('phone_otps')->where('id', $otp->id)->update($update);

            return false;
        }

        // Consume this one and anything else still open for the number.
        DB::table('phone_otps')->where('phone', $phone)->whereNull('used_at')->update(['used_at' => now(), 'updated_at' => now()]);

        return true;
    }

    private function enforcePhoneCaps(string $phone): void
    {
        $recent = DB::table('phone_otps')
            ->where('phone', $phone)
            ->where('created_at', '>=', now()->subSeconds(self::RESEND_SECONDS))
            ->orderByDesc('id')
            ->value('created_at');

        if ($recent) {
            $retryAfter = max(1, self::RESEND_SECONDS - (now()->getTimestamp() - \Carbon\Carbon::parse($recent)->getTimestamp()));

            throw $this->throttled("Try again in {$retryAfter}s.", $retryAfter);
        }

        $lastHour = DB::table('phone_otps')->where('phone', $phone)->where('created_at', '>=', now()->subHour())->count();
        if ($lastHour >= self::PER_PHONE_PER_HOUR) {
            throw $this->throttled('Too many codes requested for this number. Try again in an hour.', 3600);
        }

        $lastDay = DB::table('phone_otps')->where('phone', $phone)->where('created_at', '>=', now()->subDay())->count();
        if ($lastDay >= self::PER_PHONE_PER_DAY) {
            throw $this->throttled('Daily limit reached for this number. Please contact support.', 86400);
        }
    }

    private function throttled(string $message, int $retryAfter): ValidationException
    {
        $e = ValidationException::withMessages(['phone' => [$message]])->status(429);
        $e->response = response()->json([
            'message' => 'Please wait before requesting another code.',
            'errors' => ['phone' => [$message]],
            'code' => 'too_many_requests',
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', (string) $retryAfter);

        return $e;
    }

    /** HMAC-SHA256 keyed by the app key; bound to the phone so a hash can't be replayed across numbers. */
    private function hash(string $phone, string $code): string
    {
        return hash_hmac('sha256', $phone.'|'.$code, (string) config('app.key'));
    }
}
