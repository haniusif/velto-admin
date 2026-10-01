<?php

namespace App\Services\Notifications;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Which admin alerts go out on which channel, and to whom. Stored as
 * AppSetting rows in the `alerts` group, edited on the Admin alerts page.
 */
class AdminAlertSettings
{
    public const CHANNEL_BELL = 'bell';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';

    public const CHANNELS = [self::CHANNEL_BELL, self::CHANNEL_EMAIL, self::CHANNEL_SMS];

    /**
     * Every alert, with its channels out of the box. SMS costs credit per
     * message, so it starts on only for what needs someone to act now.
     */
    public const EVENTS = [
        'booking_confirmed' => ['bell' => true, 'email' => true, 'sms' => true],
        'needs_worker' => ['bell' => true, 'email' => true, 'sms' => true],
        'booking_cancelled' => ['bell' => true, 'email' => true, 'sms' => false],
        'booking_rescheduled' => ['bell' => true, 'email' => false, 'sms' => false],
        'worker_assigned' => ['bell' => true, 'email' => false, 'sms' => false],
        'worker_on_the_way' => ['bell' => true, 'email' => false, 'sms' => false],
        'worker_arrived' => ['bell' => true, 'email' => false, 'sms' => false],
        'job_started' => ['bell' => true, 'email' => false, 'sms' => false],
        'job_completed' => ['bell' => true, 'email' => false, 'sms' => false],
        'customer_registered' => ['bell' => true, 'email' => true, 'sms' => false],
    ];

    private const CACHE_KEY = 'alerts.settings';

    /** @var array<string,?string> */
    private array $values;

    public function __construct()
    {
        $raw = Cache::remember(self::CACHE_KEY, 300, fn () => AppSetting::group('alerts'));

        $this->values = [];
        foreach ($raw as $key => $value) {
            $this->values[str_starts_with($key, 'alerts.') ? substr($key, 7) : $key] = $value;
        }
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, array<string, bool>> event => channel => on */
    public function matrix(): array
    {
        $stored = json_decode($this->values['matrix'] ?? '', true) ?: [];

        $matrix = [];
        foreach (self::EVENTS as $event => $defaults) {
            foreach (self::CHANNELS as $channel) {
                $matrix[$event][$channel] = (bool) ($stored[$event][$channel] ?? $defaults[$channel]);
            }
        }

        return $matrix;
    }

    public function enabled(string $event, string $channel): bool
    {
        return $this->matrix()[$event][$channel] ?? false;
    }

    /** @return list<string> */
    public function emails(): array
    {
        return array_values(array_filter(
            self::split($this->values['emails'] ?? ''),
            fn (string $e) => filter_var($e, FILTER_VALIDATE_EMAIL) !== false,
        ));
    }

    /** @return list<string> */
    public function phones(): array
    {
        return self::split($this->values['phones'] ?? '');
    }

    public function language(): string
    {
        return in_array($this->values['language'] ?? null, ['ar', 'en'], true) ? $this->values['language'] : 'ar';
    }

    public function raw(string $key): string
    {
        return (string) ($this->values[$key] ?? '');
    }

    /** @return list<string> */
    private static function split(string $list): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', $list) ?: [])));
    }
}
