<?php

namespace App\Services\Notifications;

use App\Models\CustomerDevice;
use App\Models\WorkerDevice;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FCM push transport (HTTP v1 API). Config-gated: when no service-account /
 * project is configured for an audience it no-ops with a debug log, so the
 * dispatch flow works without Firebase.
 *
 * The customer and worker apps live in separate Firebase projects, so every
 * send is scoped to an audience — that picks the project, the service account,
 * the Android channel, and which device table to prune. Sending a token through
 * the wrong project fails and would prune a perfectly valid token, so the
 * audience must always match where the token came from.
 */
class PushSender
{
    public const AUDIENCE_CUSTOMER = 'customer';
    public const AUDIENCE_WORKER = 'worker';

    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    public function configured(string $audience = self::AUDIENCE_CUSTOMER): bool
    {
        $config = $this->config($audience);

        return filled($config['project'])
            && is_string($config['credentials']) && is_file($config['credentials']);
    }

    /**
     * @param  array<int,string>  $tokens  device tokens, all belonging to $audience
     * @param  array<string,mixed>  $data
     */
    public function send(
        array $tokens, string $title, string $body,
        array $data = [], string $audience = self::AUDIENCE_CUSTOMER,
    ): void {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) {
            return;
        }

        if (! $this->configured($audience)) {
            Log::debug('[push] skipped (FCM not configured)', [
                'audience' => $audience, 'title' => $title, 'count' => count($tokens),
            ]);

            return;
        }

        $config = $this->config($audience);

        try {
            $accessToken = $this->accessToken($audience, $config['credentials']);
        } catch (\Throwable $e) {
            Log::warning('[push] auth failed', ['audience' => $audience, 'error' => $e->getMessage()]);

            return;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$config['project']}/messages:send";

        foreach ($tokens as $token) {
            $this->sendOne($url, $accessToken, $token, $title, $body, $data, $config, $audience);
        }
    }

    /**
     * @param  array<string,mixed>  $data
     * @param  array<string,mixed>  $config
     */
    private function sendOne(
        string $url, string $accessToken, string $token,
        string $title, string $body, array $data, array $config, string $audience,
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
                                'channel_id' => $config['channel'],
                                'sound' => $config['sound'],
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => ['sound' => $config['sound'].'.caf'],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                $status = $response->json('error.status');
                if ($this->isDeadToken($status, (string) $response->json('error.message'))) {
                    $this->pruneToken($audience, $token);
                }
                Log::warning('[push] send failed', [
                    'audience' => $audience, 'status' => $status, 'body' => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[push] send error', ['audience' => $audience, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Is this failure the token's fault, rather than ours?
     *
     * UNREGISTERED / NOT_FOUND always mean the token is dead. INVALID_ARGUMENT
     * does NOT: FCM also returns it for a malformed payload (a bad channel id,
     * an oversized data map), and that failure repeats for every device. Pruning
     * on it blindly would wipe the entire fleet's tokens on one bad deploy, so
     * we only treat it as fatal when FCM says the registration token is the
     * problem — the exact wording it returns for a bad token.
     */
    private function isDeadToken(?string $status, string $message): bool
    {
        if (in_array($status, ['NOT_FOUND', 'UNREGISTERED'], true)) {
            return true;
        }

        return $status === 'INVALID_ARGUMENT'
            && str_contains(strtolower($message), 'registration token');
    }

    /** Drop a dead token from the table it belongs to (never the other audience's). */
    private function pruneToken(string $audience, string $token): void
    {
        $model = $audience === self::AUDIENCE_WORKER ? WorkerDevice::class : CustomerDevice::class;
        $model::where('fcm_token', $token)->delete();
    }

    /**
     * Project / service account / channel / sound for an audience. Top-level
     * config is the customer app; the worker app overrides it wholesale.
     *
     * @return array<string,mixed>
     */
    private function config(string $audience): array
    {
        $fcm = config('services.fcm', []);

        if ($audience === self::AUDIENCE_WORKER) {
            $worker = $fcm['worker'] ?? [];

            return [
                'project' => $worker['project'] ?? null,
                'credentials' => $worker['credentials'] ?? null,
                'channel' => $worker['android_channel'] ?? 'offers',
                'sound' => $worker['sound'] ?? 'bell',
            ];
        }

        return [
            'project' => $fcm['project'] ?? null,
            'credentials' => $fcm['credentials'] ?? null,
            'channel' => $fcm['android_channel'] ?? 'booking',
            'sound' => $fcm['sound'] ?? 'bell',
        ];
    }

    /** OAuth2 access token for the FCM scope, cached per audience until shortly before expiry. */
    private function accessToken(string $audience, string $credentials): string
    {
        return Cache::remember("fcm.access_token.{$audience}", now()->addMinutes(50), function () use ($credentials) {
            $creds = new ServiceAccountCredentials(self::SCOPE, $credentials);
            $token = $creds->fetchAuthToken();

            return $token['access_token'];
        });
    }
}
