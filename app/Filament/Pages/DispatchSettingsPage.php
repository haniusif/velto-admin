<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use App\Services\Dispatch\DispatchSettings;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Live-tunable dispatch knobs (mode, strategy, thresholds, scoring weights).
 * Writes AppSetting rows in the `dispatch` group and flushes the cache. This is
 * the human-in-the-loop precursor to a learned weighting model.
 */
class DispatchSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.dispatch-settings';

    // Bound form state.
    public string $mode = 'direct';
    public string $default_strategy = 'auto';
    public bool $auto_dispatch_enabled = true;
    public bool $require_online = false;
    public int $immediate_threshold_minutes = 90;
    public int $dispatch_lead_minutes = 30;
    public int $acceptance_timeout_seconds = 120;
    public int $retry_interval_seconds = 60;
    public int $max_retries = 10;
    public int $distance_radius_km = 15;

    /** @var array<string,float> */
    public array $weights = [];

    public const STRATEGIES = [
        'auto', 'least_loaded', 'round_robin', 'nearest',
        'city_based', 'zone_based', 'skill_based', 'manual',
    ];

    public const WEIGHT_KEYS = [
        'distance', 'workload', 'jobs_today', 'rating', 'acceptance', 'same_zone', 'preferred',
    ];

    public static function getNavigationLabel(): string
    {
        return __('Dispatch Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Operations');
    }

    public function getTitle(): string
    {
        return __('Dispatch Settings');
    }

    public function mount(): void
    {
        $s = app(DispatchSettings::class);
        $this->mode = $s->string('mode');
        $this->default_strategy = $s->string('default_strategy');
        $this->auto_dispatch_enabled = $s->bool('auto_dispatch_enabled');
        $this->require_online = $s->bool('require_online');
        $this->immediate_threshold_minutes = $s->int('immediate_threshold_minutes');
        $this->dispatch_lead_minutes = $s->int('dispatch_lead_minutes');
        $this->acceptance_timeout_seconds = $s->int('acceptance_timeout_seconds');
        $this->retry_interval_seconds = $s->int('retry_interval_seconds');
        $this->max_retries = $s->int('max_retries');
        $this->distance_radius_km = $s->int('distance_radius_km');
        $this->weights = $s->weights();
    }

    public function save(): void
    {
        $scalar = [
            'mode' => $this->mode,
            'default_strategy' => $this->default_strategy,
            'auto_dispatch_enabled' => $this->auto_dispatch_enabled ? '1' : '0',
            'require_online' => $this->require_online ? '1' : '0',
            'immediate_threshold_minutes' => (string) max(0, $this->immediate_threshold_minutes),
            'dispatch_lead_minutes' => (string) max(0, $this->dispatch_lead_minutes),
            'acceptance_timeout_seconds' => (string) max(10, $this->acceptance_timeout_seconds),
            'retry_interval_seconds' => (string) max(10, $this->retry_interval_seconds),
            'max_retries' => (string) max(1, $this->max_retries),
            'distance_radius_km' => (string) max(1, $this->distance_radius_km),
        ];

        foreach ($scalar as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => "dispatch.$key"],
                ['group' => 'dispatch', 'value' => $value, 'type' => 'string'],
            );
        }

        // Normalize weights to floats and persist as JSON.
        $weights = [];
        foreach (self::WEIGHT_KEYS as $k) {
            $weights[$k] = round((float) ($this->weights[$k] ?? 0), 3);
        }
        AppSetting::updateOrCreate(
            ['key' => 'dispatch.scoring_weights'],
            ['group' => 'dispatch', 'value' => json_encode($weights), 'type' => 'json'],
        );

        DispatchSettings::flush();

        Notification::make()->success()->title(__('Dispatch settings saved'))->send();
    }

    public function weightTotal(): float
    {
        return round(array_sum(array_map(fn ($k) => (float) ($this->weights[$k] ?? 0), self::WEIGHT_KEYS)), 3);
    }
}
