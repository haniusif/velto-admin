<?php

namespace App\Services\Notifications;

use App\Models\WorkerDevice;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FCM push transport (HTTP v1 API). Config-gated: when no service-account /
 * project is configured it no-ops with a debug log, so the dispatch flow works
 * without Firebase. Once services.fcm.project + credentials exist it sends a
 * message per device token and prunes tokens FCM reports as unregistered.
 */
class PushSender
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    public function configured(): bool
    {
        $creds = config('services.fcm.credentials');

        return filled(config('services.fcm.project'))
            && is_string($creds) && is_file($creds);
    }

    /**
     * @param  array<int,string>  $tokens  device tokens
     * @param  array<string,mixed>  $data
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) {
            return;
        }

        if (! $this->configured()) {
            Log::debug('[push] skipped (FCM not configured)', ['title' => $title, 'count' => count($tokens)]);

            return;
        }

        try {
            $accessToken = $this->accessToken();
        } catch (\Throwable $e) {
            Log::warning('[push] auth failed', ['error' => $e->getMessage()]);

            return;
        }

        $project = config('services.fcm.project');
        $url = "https://fcm.googleapis.com/v1/projects/{$project}/messages:send";
        $channel = config('services.fcm.android_channel', 'offers');
        $sound = config('services.fcm.sound', 'bell');

        foreach ($tokens as $token) {
            $this->sendOne($url, $accessToken, $token, $title, $body, $data, $channel, $sound);
        }
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function sendOne(
        string $url, string $accessToken, string $token,
        string $title, string $body, array $data, string $channel, string $sound,
    ): void {
        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($url, [
                    'message' => [
                        'token' => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => array_map('strval', $data),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'channel_id' => $channel,
                                'sound' => $sound,
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => ['sound' => $sound.'.caf'],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                $status = $response->json('error.status');
                // A token that's no longer valid — drop it so we stop trying.
                if (in_array($status, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                    WorkerDevice::where('fcm_token', $token)->delete();
                }
                Log::warning('[push] send failed', ['status' => $status, 'body' => $response->json()]);
            }
        } catch (\Throwable $e) {
            Log::warning('[push] send error', ['error' => $e->getMessage()]);
        }
    }

    /** OAuth2 access token for the FCM scope, cached until shortly before expiry. */
    private function accessToken(): string
    {
        return Cache::remember('fcm.access_token', now()->addMinutes(50), function () {
            $creds = new ServiceAccountCredentials(self::SCOPE, config('services.fcm.credentials'));
            $token = $creds->fetchAuthToken();

            return $token['access_token'];
        });
    }
}
