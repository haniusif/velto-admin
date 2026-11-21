<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\PushSender;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Send one notification to a whole audience — the in-app inbox plus a push to
 * every device the audience owns.
 *
 * Sends inline rather than queued: there is no queue worker on the server, so
 * a dispatched job would sit in `jobs` forever. That caps the practical
 * audience size at a few hundred customers per send.
 */
class BulkNotificationPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.bulk-notification';

    protected static ?string $slug = 'bulk-notification';

    /** @var array<string,mixed>|null */
    public ?array $data = [];

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_WITH_APP = 'with_app';
    public const AUDIENCE_CITY = 'city';

    public static function getNavigationLabel(): string
    {
        return __('Bulk notification');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public function getTitle(): string
    {
        return __('Bulk notification');
    }

    public function mount(): void
    {
        $this->form->fill([
            'audience' => self::AUDIENCE_WITH_APP,
            'kind' => CustomerNotification::KIND_PROMO,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Audience'))
                    ->description(__('Start from a base group, then narrow it with any of the filters. They combine, and the send button shows the final recipient count. Only customers with the app installed can receive a push; everyone else still gets the in-app inbox message.'))
                    ->columns(2)
                    ->components([
                        Select::make('audience')
                            ->label(__('Send to'))
                            ->options([
                                self::AUDIENCE_WITH_APP => __('Customers with the app installed'),
                                self::AUDIENCE_ALL => __('All active customers'),
                            ])
                            ->required()
                            ->native(false)
                            ->live(),

                        Select::make('language')
                            ->label(__('Language'))
                            ->placeholder(__('Any language'))
                            ->options([
                                'ar' => __('Arabic'),
                                'en' => __('English'),
                            ])
                            ->native(false)
                            ->live(),

                        Select::make('city')
                            ->label(__('City'))
                            ->placeholder(__('All cities'))
                            ->options(fn (): array => Customer::query()
                                ->whereNotNull('city')
                                ->where('city', '!=', '')
                                ->distinct()
                                ->orderBy('city')
                                ->pluck('city', 'city')
                                ->all())
                            ->searchable()
                            ->native(false)
                            ->live(),

                        Select::make('area')
                            ->label(__('Area'))
                            ->placeholder(__('All areas'))
                            ->options(fn ($get): array => Customer::query()
                                ->whereNotNull('area')
                                ->where('area', '!=', '')
                                ->when($get('city'), fn (Builder $q, $c) => $q->where('city', $c))
                                ->distinct()
                                ->orderBy('area')
                                ->pluck('area', 'area')
                                ->all())
                            ->searchable()
                            ->native(false)
                            ->live(),

                        Select::make('gender')
                            ->label(__('Gender'))
                            ->placeholder(__('Any gender'))
                            ->options([
                                'male' => __('Male'),
                                'female' => __('Female'),
                            ])
                            ->native(false)
                            ->live(),

                        Select::make('subscription')
                            ->label(__('Subscription'))
                            ->placeholder(__('All'))
                            ->options([
                                'active' => __('Has active plan'),
                                'none' => __('No active plan'),
                            ])
                            ->native(false)
                            ->live(),

                        Select::make('wallet')
                            ->label(__('Wallet'))
                            ->placeholder(__('All'))
                            ->options([
                                'has' => __('Has balance'),
                                'none' => __('No balance'),
                            ])
                            ->native(false)
                            ->live(),

                        Placeholder::make('recipient_count')
                            ->label(__('Recipients'))
                            ->content(fn ($get): string => number_format($this->buildQuery([
                                'audience' => $get('audience'),
                                'language' => $get('language'),
                                'city' => $get('city'),
                                'area' => $get('area'),
                                'gender' => $get('gender'),
                                'subscription' => $get('subscription'),
                                'wallet' => $get('wallet'),
                            ])->count()).' '.__('customers'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Message'))
                    ->description(__('Each customer receives the language they picked in the app, so fill both.'))
                    ->columns(2)
                    ->components([
                        Select::make('kind')
                            ->label(__('Kind'))
                            ->options([
                                CustomerNotification::KIND_PROMO => __('Promo'),
                                CustomerNotification::KIND_BOOKING => __('Booking'),
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label(__('Title (English)'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('title_ar')
                            ->label(__('Title (Arabic)'))
                            ->required()
                            ->maxLength(255),

                        Textarea::make('body')
                            ->label(__('Body (English)'))
                            ->required()
                            ->rows(3),

                        Textarea::make('body_ar')
                            ->label(__('Body (Arabic)'))
                            ->required()
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label(__('Send now'))
                ->requiresConfirmation()
                ->modalHeading(__('Send this notification?'))
                ->modalDescription(fn (): string => __('It will reach :count customers and cannot be undone.', [
                    'count' => $this->recipientQuery()->count(),
                ]))
                ->action('send'),
        ];
    }

    /** How many customers the current audience selection resolves to. */
    public function getRecipientCountProperty(): int
    {
        return $this->recipientQuery()->count();
    }

    public function send(): void
    {
        $state = $this->form->getState();

        $ids = $this->recipientQuery()->pluck('id');

        if ($ids->isEmpty()) {
            Notification::make()
                ->title(__('No customers matched that audience'))
                ->warning()
                ->send();

            return;
        }

        $sent = app(NotificationDispatcher::class)->customerAnnouncement(
            $ids,
            $state['kind'],
            $state['title'],
            $state['title_ar'],
            $state['body'],
            $state['body_ar'],
            ['source' => 'admin_bulk'],
        );

        $pushLive = app(PushSender::class)->configured(PushSender::AUDIENCE_CUSTOMER);

        Notification::make()
            ->title(__('Sent to :count customers', ['count' => $sent]))
            ->body($pushLive
                ? __('Delivered to the in-app inbox and pushed to their devices.')
                : __('Delivered to the in-app inbox only — push is not configured on this server.'))
            ->success()
            ->send();
    }

    /**
     * The audience, resolved to a customer query. Kept in one place so the
     * confirmation count and the actual send can never disagree.
     */
    protected function recipientQuery(): Builder
    {
        return $this->buildQuery($this->form->getState());
    }

    /**
     * Resolve an audience selection (base group + optional filters) to a
     * customer query. Shared by the live recipient count, the confirmation
     * count and the actual send, so all three can never disagree.
     *
     * @param  array<string,mixed>  $state
     */
    protected function buildQuery(array $state): Builder
    {
        $query = Customer::query()->where('status', 'active');

        // Base group: everyone active, or only those with the app (push-capable).
        if (($state['audience'] ?? self::AUDIENCE_WITH_APP) === self::AUDIENCE_WITH_APP) {
            $query->whereHas('devices');
        }

        // Optional narrowing filters — each applies only when chosen, and they
        // combine (AND) so the audience shrinks to the intersection.
        return $query
            ->when($state['language'] ?? null,
                fn (Builder $q, $v) => $q->where('preferred_language', $v))
            ->when($state['city'] ?? null,
                fn (Builder $q, $v) => $q->where('city', $v))
            ->when($state['area'] ?? null,
                fn (Builder $q, $v) => $q->where('area', $v))
            ->when($state['gender'] ?? null,
                fn (Builder $q, $v) => $q->where('gender', $v))
            ->when(($state['subscription'] ?? null) === 'active',
                fn (Builder $q) => $q->whereHas('packages', fn ($p) => $p->where('status', 'active')))
            ->when(($state['subscription'] ?? null) === 'none',
                fn (Builder $q) => $q->whereDoesntHave('packages', fn ($p) => $p->where('status', 'active')))
            ->when(($state['wallet'] ?? null) === 'has',
                fn (Builder $q) => $q->where('wallet_balance', '>', 0))
            ->when(($state['wallet'] ?? null) === 'none',
                fn (Builder $q) => $q->where('wallet_balance', '<=', 0));
    }
}
