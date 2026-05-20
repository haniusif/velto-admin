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

        return [
            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'response' => json_decode((string) $response, true),
        ];
    }

    public function isConfigured(): bool
    {
        return filled($this->appId) && filled($this->appSecret);
    }
}
