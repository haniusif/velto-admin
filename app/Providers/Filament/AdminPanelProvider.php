<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#8863E5'),
            ])
            ->brandName('Velto Admin')
            ->font('Cairo', provider: LocalFontProvider::class)
            ->darkMode()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make(fn (): string => __('Operations'))->collapsible(),
                NavigationGroup::make(fn (): string => __('Customers'))->collapsible(),
                NavigationGroup::make(fn (): string => __('Marketing'))->collapsible(),
                NavigationGroup::make(fn (): string => __('Catalog'))->collapsed(),
                NavigationGroup::make(fn (): string => __('Locations'))->collapsed(),
                NavigationGroup::make(fn (): string => __('Team & access'))->collapsed(),
                NavigationGroup::make(fn (): string => __('Lookups'))->collapsed(),
                NavigationGroup::make(fn (): string => __('Settings'))->collapsed(),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        // Every price in this business is riyals, but Filament's built-in
        // default is USD — so a ->money() that forgot to name a currency read
        // a 35 SAR wash as $35. Not obviously broken, just wrong by a factor
        // of 3.75, on pages used to settle refunds. Set once for every table
        // and every infolist so the mistake cannot be made one entry at a
        // time.
        Table::configureUsing(fn (Table $table) => $table->defaultCurrency('SAR'));
        Schema::configureUsing(fn (Schema $schema) => $schema->defaultCurrency('SAR'));

        // Timestamps are stored as true UTC instants — created_at, paid_at,
        // completed_at and the rest — so the admin was showing staff a clock
        // three hours behind the one on the wall. Display converts to Riyadh.
        //
        // This does NOT apply to a booking's scheduled_at or a slot's
        // date/start_time: those hold Riyadh wall-clock digits stored naively,
        // are already correct as written, and are pinned to app.timezone at
        // each call site so this conversion cannot shift them a second time.
        FilamentTimezone::set(config('app.business_timezone'));
    }
}
