<?php

namespace App\Services\Dispatch;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Typed, cached access to every knob the dispatch engine reads. Values are
 * stored as AppSetting rows in the `dispatch` group (editable in the admin);
 * anything unset falls back to the defaults below — no magic numbers in code.
 */
class DispatchSettings
{
    private const CACHE_KEY = 'dispatch.settings';

    private const DEFAULTS = [
        'mode' => 'direct',                  // direct | offer
        'default_strategy' => 'least_loaded',
        'auto_dispatch_enabled' => true,
        'require_online' => false,           // Phase 1: workers may not toggle duty yet
        'immediate_threshold_minutes' => 90,
        'dispatch_lead_minutes' => 30,
        'acceptance_timeout_seconds' => 120,
        'retry_interval_seconds' => 60,
        'max_retries' => 10,
        'max_reassignments' => 5,
        'distance_radius_km' => 15,
        'reject_cooldown_count' => 3,
        'reject_cooldown_hours' => 1,
    ];

    /** @var array<string,mixed> */
    private array $values;

    public function __construct()
    {
        // AppSetting stores keys with the group prefix (e.g. "dispatch.mode");
        // strip it so stored values line up with the short DEFAULTS keys.
        $raw = Cache::remember(self::CACHE_KEY, 300, fn () => AppSetting::group('dispatch'));

        $stored = [];
        foreach ($raw as $key => $value) {
            $short = str_starts_with($key, 'dispatch.') ? substr($key, 9) : $key;
            $stored[$short] = $value;
        }

        $this->values = array_merge(self::DEFAULTS, $stored);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function string(string $key): string
    {
        return (string) ($this->values[$key] ?? self::DEFAULTS[$key] ?? '');
    }

    public function int(string $key): int
    {
        return (int) ($this->values[$key] ?? self::DEFAULTS[$key] ?? 0);
    }

    public function bool(string $key): bool
    {
        $v = $this->values[$key] ?? self::DEFAULTS[$key] ?? false;

        return filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }

    public function autoDispatchEnabled(): bool
    {
        return $this->bool('auto_dispatch_enabled');
    }

    public function usesOffers(): bool
    {
        return $this->string('mode') === 'offer';
    }
}
