<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FCM push transport. Config-gated: when no server key is set (services.fcm.key)
 * it no-ops with a debug log, so the whole dispatch flow works today without
 * Firebase. Dropping in the key + a device-token store lights it up unchanged.
 */
class PushSender
{
    public function configured(): bool
    {
        return filled(config('services.fcm.key'));
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
            Log::debug('[push] skipped (FCM not configured)', compact('title', 'tokens'));

            return;
        }

        try {
            Http::withToken(config('services.fcm.key'))
                ->acceptJson()
                ->post('https://fcm.googleapis.com/v1/projects/'.config('services.fcm.project').'/messages:send', [
                    'message' => [
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => array_map('strval', $data),
                        'tokens' => $tokens, // adapt to your FCM send shape when wiring
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('[push] send failed', ['error' => $e->getMessage()]);
        }
    }
}
