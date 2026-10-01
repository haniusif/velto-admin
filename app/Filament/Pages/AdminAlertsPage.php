<?php

namespace App\Filament\Pages;

use App\Jobs\SendAdminAlert;
use App\Models\AppSetting;
use App\Services\JawalySMSService;
use App\Services\Notifications\AdminAlertSettings;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Notification as LaravelNotification;

/**
 * Which events reach the team, on which channel (panel bell, email, SMS), and
 * the addresses and numbers they go to.
 */
class AdminAlertsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'admin-alerts';

    protected string $view = 'filament.pages.admin-alerts';

    /** @var array<string, array<string, bool>> */
    public array $matrix = [];

    public string $emails = '';

    public string $phones = '';

    public string $language = 'ar';

    public static function getNavigationLabel(): string
    {
        return __('Admin alerts');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Admin alerts');
    }

    public function mount(): void
    {
        $s = app(AdminAlertSettings::class);
        $this->matrix = $s->matrix();
        $this->emails = $s->raw('emails');
        $this->phones = $s->raw('phones');
        $this->language = $s->language();
    }

    public function save(): void
    {
        $matrix = [];
        foreach (AdminAlertSettings::EVENTS as $event => $_) {
            foreach (AdminAlertSettings::CHANNELS as $channel) {
                $matrix[$event][$channel] = (bool) ($this->matrix[$event][$channel] ?? false);
            }
        }

        $values = [
            'matrix' => [json_encode($matrix), 'json'],
            'emails' => [trim($this->emails), 'string'],
            'phones' => [trim($this->phones), 'string'],
            'language' => [in_array($this->language, ['ar', 'en'], true) ? $this->language : 'ar', 'string'],
        ];

        foreach ($values as $key => [$value, $type]) {
            AppSetting::updateOrCreate(
                ['key' => "alerts.$key"],
                ['group' => 'alerts', 'value' => $value, 'type' => $type],
            );
        }

        AdminAlertSettings::flush();
        $this->mount();

        Notification::make()->success()->title(__('Admin alerts saved'))->send();
    }

    /** Proves the wiring end to end, to the lists as saved. */
    public function sendTest(): void
    {
        $s = app(AdminAlertSettings::class);
        $lang = $s->language();
        $title = __('Test alert from Velto Admin', [], $lang);
        $body = __('If you can read this, alerts reach you.', [], $lang);

        LaravelNotification::sendNow(auth()->user(),
            Notification::make()->title($title)->body($body)->icon('heroicon-o-bell-alert')->toDatabase());

        if ($s->emails() !== [] || $s->phones() !== []) {
            SendAdminAlert::dispatch($title, $body, url('/admin'), $s->emails(), $s->phones());
        }

        Notification::make()->success()
            ->title(__('Test sent'))
            ->body(__('Bell now; email and SMS within about a minute.'))
            ->send();
    }

    public function mailReady(): bool
    {
        return ! in_array(config('mail.default'), ['log', 'array'], true);
    }

    public function smsReady(): bool
    {
        return app(JawalySMSService::class)->isConfigured();
    }
}
