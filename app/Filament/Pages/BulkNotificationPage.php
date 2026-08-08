<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\PushSender;
use BackedEnum;
use Filament\Actions\Action;
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
                    ->description(__('Only customers with the app installed can receive a push. Everyone else still gets the in-app inbox message.'))
                    ->columns(2)
                    ->components([
                        Select::make('audience')
                            ->label(__('Send to'))
                            ->options([
                                self::AUDIENCE_WITH_APP => __('Customers with the app installed'),
                                self::AUDIENCE_ALL => __('All active customers'),
                                self::AUDIENCE_CITY => __('Active customers in one city'),
                            ])
                            ->required()
                            ->live()
                            ->native(false),

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
        $state = $this->form->getState();
        $query = Customer::query()->where('status', 'active');

        return match ($state['audience'] ?? self::AUDIENCE_WITH_APP) {
            self::AUDIENCE_WITH_APP => $query->whereHas('devices'),
            self::AUDIENCE_CITY => $query->where('city', $state['city'] ?? null),
            default => $query,
        };
    }
}
