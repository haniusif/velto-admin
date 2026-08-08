<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Services\JawalySMSService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

/**
 * Send an SMS to customers through the same 4jawaly account the OTPs use.
 *
 * SMS costs money per recipient and cannot be recalled, so the audience count
 * is shown on the confirmation step before anything leaves the building.
 */
class SendSmsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.send-sms';

    protected static ?string $slug = 'send-sms';

    /** @var array<string,mixed>|null */
    public ?array $data = [];

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_CITY = 'city';
    public const AUDIENCE_PICK = 'pick';
    public const AUDIENCE_MANUAL = 'manual';

    public static function getNavigationLabel(): string
    {
        return __('Send SMS');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public function getTitle(): string
    {
        return __('Send SMS');
    }

    public function mount(): void
    {
        $this->form->fill(['audience' => self::AUDIENCE_PICK]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Recipients'))
                    ->components([
                        Select::make('audience')
                            ->label(__('Send to'))
                            ->options([
                                self::AUDIENCE_PICK => __('Pick customers'),
                                self::AUDIENCE_CITY => __('Active customers in one city'),
                                self::AUDIENCE_ALL => __('All active customers'),
                                self::AUDIENCE_MANUAL => __('Type numbers manually'),
                            ])
                            ->required()
                            ->live()
                            ->native(false),

                        Select::make('customer_ids')
                            ->label(__('Customers'))
                            ->options(fn (): array => Customer::query()
                                ->whereNotNull('phone')
                                ->orderBy('name')
                                ->limit(500)
                                ->pluck('name', 'id')
                                ->all())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(fn ($get): bool => $get('audience') === self::AUDIENCE_PICK)
                            ->visible(fn ($get): bool => $get('audience') === self::AUDIENCE_PICK),

                        Select::make('city')
                            ->label(__('City'))
                            ->options(fn (): array => Customer::query()
                                ->whereNotNull('city')
                                ->distinct()
                                ->orderBy('city')
                                ->pluck('city', 'city')
                                ->all())
                            ->searchable()
                            ->native(false)
                            ->required(fn ($get): bool => $get('audience') === self::AUDIENCE_CITY)
                            ->visible(fn ($get): bool => $get('audience') === self::AUDIENCE_CITY),

                        Textarea::make('numbers')
                            ->label(__('Numbers'))
                            ->helperText(__('One per line, or separated by commas. International format, e.g. +9665xxxxxxxx.'))
                            ->rows(4)
                            ->required(fn ($get): bool => $get('audience') === self::AUDIENCE_MANUAL)
                            ->visible(fn ($get): bool => $get('audience') === self::AUDIENCE_MANUAL),
                    ]),

                Section::make(__('Message'))
                    ->description(__('Arabic text costs more per message than Latin — an Arabic SMS splits every 70 characters instead of 160.'))
                    ->components([
                        Textarea::make('message')
                            ->label(__('Message'))
                            ->required()
                            ->rows(4)
                            ->maxLength(600)
                            ->live(debounce: 400)
                            ->helperText(fn ($state): string => __(':chars characters', [
                                'chars' => mb_strlen((string) $state),
                            ])),
                    ]),
            ])
            ->statePath('data');
    }

    /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label(__('Send SMS'))
                ->requiresConfirmation()
                ->modalHeading(__('Send this SMS?'))
                ->modalDescription(fn (): string => __('It will be sent to :count numbers and will be charged to the SMS account.', [
                    'count' => $this->recipientNumbers()->count(),
                ]))
                ->disabled(fn (): bool => ! app(JawalySMSService::class)->isConfigured())
                ->action('send'),
        ];
    }

    public function send(): void
    {
        $state = $this->form->getState();
        $numbers = $this->recipientNumbers();

        if ($numbers->isEmpty()) {
            Notification::make()
                ->title(__('No numbers matched that audience'))
                ->warning()
                ->send();

            return;
        }

        $result = app(JawalySMSService::class)->sendSMS($numbers->all(), $state['message']);

        if (($result['success'] ?? false) === true) {
            Notification::make()
                ->title(__('Sent to :count numbers', ['count' => $numbers->count()]))
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('SMS failed'))
            ->body((string) ($result['error'] ?? __('The SMS gateway rejected the request.')))
            ->danger()
            ->send();
    }

    /**
     * The audience, resolved to a de-duplicated list of phone numbers. Shared
     * by the confirmation count and the send so the two cannot disagree.
     *
     * @return Collection<int,string>
     */
    protected function recipientNumbers(): Collection
    {
        $state = $this->form->getState();
        $audience = $state['audience'] ?? self::AUDIENCE_PICK;

        if ($audience === self::AUDIENCE_MANUAL) {
            return collect(preg_split('/[\s,;]+/', (string) ($state['numbers'] ?? '')) ?: [])
                ->map(fn (string $n): string => trim($n))
                ->filter()
                ->unique()
                ->values();
        }

        $query = Customer::query()->whereNotNull('phone');

        $query = match ($audience) {
            self::AUDIENCE_PICK => $query->whereIn('id', $state['customer_ids'] ?? []),
            self::AUDIENCE_CITY => $query->where('status', 'active')->where('city', $state['city'] ?? null),
            default => $query->where('status', 'active'),
        };

        return $query->pluck('phone')
            ->map(fn (?string $p): string => trim((string) $p))
            ->filter()
            ->unique()
            ->values();
    }
}
