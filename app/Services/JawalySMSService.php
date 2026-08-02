<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class JawalySMSService
{
    private string $baseUrl;
    private ?string $appId;
    private ?string $appSecret;
    private string $sender;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.jawaly.base_url', 'https://api-sms.4jawaly.com/api/v1/'), '/').'/';
        $this->appId = config('services.jawaly.app_id');
        $this->appSecret = config('services.jawaly.app_secret');
        $this->sender = (string) config('services.jawaly.sender', 'Velto');
    }

    public function sendOtp(string $phone, string $otp): array
    {
        return $this->sendSMS([$phone], "Your Velto verification code is {$otp}", $this->sender);
    }

    /**
     * Send a message to one or more numbers in a single API call.
     *
     * @param  array<string>  $numbers
     */
    public function sendSMS(array $numbers, string $message, ?string $sender = null): array
    {
        $sender = $sender ?: $this->sender;

        if (! $this->isConfigured()) {
            Log::warning('Jawaly SMS skipped — credentials not set', [
                'numbers' => $numbers,
                'message' => $message,
            ]);

            return ['success' => false, 'error' => 'sms_not_configured'];
        }

        $url = $this->baseUrl.'account/area/sms/send';

        // 4jawaly expects bare international digits (e.g. 9665xxxxxxxx) — a
        // leading '+' or spaces cause the message to be rejected server-side.
        $numbers = array_values(array_filter(array_map(
            static fn (string $n): string => preg_replace('/\D+/', '', $n) ?? '',
            $numbers,
        ), static fn (string $n): bool => $n !== ''));

        $payload = [
            'messages' => [[
                'text' => $message,
                'numbers' => $numbers,
                'sender' => $sender,
            ]],
        ];

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($this->appId.':'.$this->appSecret),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Log::error('Jawaly SMS curl error', ['error' => $error]);

            return ['success' => false, 'error' => $error];
        }

        curl_close($ch);

        $body = json_decode((string) $response, true);

        // A 2xx from 4jawaly is necessary but NOT sufficient: the provider
        // returns 200 even when a message is rejected (bad sender, no balance,
        // invalid number). The real outcome lives in the body, so inspect it —
        // otherwise a silent delivery failure looks like success.
        $httpOk = $status >= 200 && $status < 300;
        $success = $httpOk && $this->bodyIndicatesSuccess($body);

        if (! $success) {
            Log::warning('Jawaly SMS not delivered', [
                'status' => $status,
                'response' => $body,
            ]);
        }

        return [
            'success' => $success,
            'status' => $status,
            'response' => $body,
        ];
    }

    /**
     * Interpret a 4jawaly send response. Treats explicit failure signals
     * (success=false, total_failed>0, total_success==0, per-message err_text)
     * as failures. When the body carries no recognisable status fields, it
     * defers to the HTTP status the caller already checked.
     *
     * @param  mixed  $body
     */
    private function bodyIndicatesSuccess($body): bool
    {
        if (! is_array($body)) {
            // Non-JSON / empty body on a 2xx — can't confirm, don't assume.
            return false;
        }

        if (array_key_exists('success', $body)) {
            return (bool) $body['success'] && (int) ($body['total_failed'] ?? 0) === 0;
        }

        if (array_key_exists('total_failed', $body) && (int) $body['total_failed'] > 0) {
            return false;
        }

        if (array_key_exists('total_success', $body)) {
            return (int) $body['total_success'] > 0;
        }

        foreach ((array) ($body['messages'] ?? []) as $msg) {
            if (! empty($msg['err_text'])) {
                return false;
            }
        }

        // No known status fields present — fall back to the HTTP 2xx result.
        return true;
    }

    public function isConfigured(): bool
    {
        return filled($this->appId) && filled($this->appSecret);
    }

    /**
     * Remaining SMS credits across the account's open packages.
     *
     * 4jawaly bills per message from a prepaid points balance. Running out is
     * silent from our side — sendSMS still returns HTTP 200 — so the number is
     * worth watching before customers stop receiving codes.
     *
     * @return array{configured:bool, remaining:int|null, total:int|null, expires_at:string|null, error:string|null}
     */
    public function balance(): array
    {
        $empty = ['configured' => false, 'remaining' => null, 'total' => null, 'expires_at' => null, 'error' => null];

        if (! $this->isConfigured()) {
            return $empty;
        }

        $body = $this->get('account/area/me/packages');

        if ($body === null) {
            return ['configured' => true, 'remaining' => null, 'total' => null, 'expires_at' => null, 'error' => 'unreachable'];
        }

        $packages = (array) data_get($body, 'items.data', []);

        // Only packages still open count toward what we can actually send.
        $open = array_filter($packages, static fn (array $p): bool => (int) ($p['is_open'] ?? 0) === 1);

        if ($open === []) {
            return ['configured' => true, 'remaining' => 0, 'total' => 0, 'expires_at' => null, 'error' => null];
        }

        $remaining = array_sum(array_map(static fn (array $p): int => (int) ($p['current_points'] ?? 0), $open));
        $total = array_sum(array_map(static fn (array $p): int => (int) ($p['package_points'] ?? 0), $open));

        // The soonest expiry is the one that matters — credits die with it.
        $expiries = array_filter(array_map(static fn (array $p) => $p['expire_at'] ?? null, $open));
        sort($expiries);

        return [
            'configured' => true,
            'remaining' => $remaining,
            'total' => $total,
            'expires_at' => $expiries[0] ?? null,
            'error' => null,
        ];
    }

    /** GET a JSON endpoint with the account credentials. Null on any failure. */
    private function get(string $path): ?array
    {
        $ch = curl_init($this->baseUrl.ltrim($path, '/'));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                // 4jawaly rejects a GET without this, with a 400 that says so.
                'Content-Type: application/json',
                'Authorization: Basic '.base64_encode($this->appId.':'.$this->appSecret),
            ],
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        if ($error !== null || $status < 200 || $status >= 300) {
            Log::warning('Jawaly GET failed', ['path' => $path, 'status' => $status, 'error' => $error]);

            return null;
        }

        return json_decode((string) $response, true);
    }
}
