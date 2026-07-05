<?php

namespace App\Providers;

use App\Services\ARB\ArbCrypto;
use App\Services\ARB\ArbGateway;
use BezhanSalleh\LanguageSwitch\Enums\Placement;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArbGateway::class, function ($app): ArbGateway {
            $config = $app['config']->get('services.arb', []);

            return new ArbGateway(
                new ArbCrypto((string) ($config['resource_key'] ?? '')),
                $config,
            );
        });
    }

    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch
                ->locales(['en', 'ar'])
                ->labels([
                    'en' => 'English',
                    'ar' => 'العربية',
                ])
                ->visible(outsidePanels: true)
                ->outsidePanelRoutes([
                    'filament.admin.auth.login',
                ])
                ->outsidePanelPlacement(Placement::TopRight)
                ->circular()
                ->displayLocale('en');
        });

        FilamentView::registerRenderHook(
            'panels::body.start',
            fn (): string => Blade::render(<<<'BLADE'
                <script>
                    (function () {
                        var dir = @json(app()->getLocale() === 'ar' ? 'rtl' : 'ltr');
                        document.documentElement.setAttribute('dir', dir);
                        document.documentElement.setAttribute('lang', @json(app()->getLocale()));
                    })();
                </script>
            BLADE),
        );

        // Inject brand fonts (Poppins for Latin, Cairo for Arabic) into the
        // admin panel so every page picks up the Velto identity.
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => <<<'HTML'
                <style>
                    @font-face {
                        font-family: 'Poppins';
                        src: url('/fonts/poppins/Poppins-Regular.ttf') format('truetype');
                        font-weight: 400; font-style: normal; font-display: swap;
                    }
                    @font-face {
                        font-family: 'Poppins';
                        src: url('/fonts/poppins/Poppins-Medium.ttf') format('truetype');
                        font-weight: 500; font-style: normal; font-display: swap;
                    }
                    @font-face {
                        font-family: 'Poppins';
                        src: url('/fonts/poppins/Poppins-SemiBold.ttf') format('truetype');
                        font-weight: 600; font-style: normal; font-display: swap;
                    }
                    @font-face {
                        font-family: 'Poppins';
                        src: url('/fonts/poppins/Poppins-Bold.ttf') format('truetype');
                        font-weight: 700; font-style: normal; font-display: swap;
                    }
                    @font-face {
                        font-family: 'Poppins';
                        src: url('/fonts/poppins/Poppins-Italic.ttf') format('truetype');
                        font-weight: 400; font-style: italic; font-display: swap;
                    }
                    @font-face {
                        font-family: 'Cairo';
                        src: url('/fonts/cairo/Cairo-Variable.ttf') format('truetype-variations');
                        font-weight: 200 1000; font-style: normal; font-display: swap;
                    }

                    /* Apply globally; Arabic gets Cairo via the dir attribute. */
                    html, body, .fi-body, .fi-main,
                    [x-data], [wire\:id] {
                        font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
                    }
                    html[lang="ar"], html[lang="ar"] body,
                    html[dir="rtl"], html[dir="rtl"] body {
                        font-family: 'Cairo', 'Poppins', system-ui, sans-serif;
                    }

                    /* Filament uses CSS vars for its default font in some places. */
                    :root {
                        --font-sans: 'Poppins', system-ui, sans-serif;
                    }
                    html[lang="ar"] :root, html[dir="rtl"] :root {
                        --font-sans: 'Cairo', 'Poppins', system-ui, sans-serif;
                    }
                </style>
            HTML,
        );

        // Branded split-screen redesign for the auth (login) screen. Injected
        // at the start of the simple layout so the brand pane becomes the
        // left column and Filament's login card the right column.
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_LAYOUT_START,
            fn (): string => <<<'HTML'
                <style>
                    /* ===== Velto branded auth layout ===== */
                    .fi-body:has(.velto-brand) { min-height: 100dvh; }
                    .fi-simple-layout {
                        min-height: 100dvh;
                        height: 100dvh;
                        display: flex;
                        flex-direction: row;
                        align-items: stretch;
                    }
                    .fi-simple-layout .fi-simple-main-ctn {
                        flex: 1 1 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 2.5rem 1.5rem;
                    }
                    .fi-simple-layout .fi-simple-main {
                        width: 100%;
                        max-width: 25rem;
                        border-radius: 1.25rem;
                        box-shadow:
                            0 24px 50px -22px rgba(88, 99, 229, .45),
                            0 8px 22px -14px rgba(15, 10, 40, .35);
                    }

                    /* Brand pane (left column) */
                    .velto-brand {
                        flex: 1 1 50%;
                        position: relative;
                        overflow: hidden;
                        isolation: isolate;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        gap: 1.5rem;
                        padding: clamp(2.5rem, 5vw, 4.5rem);
                        color: #fff;
                        background:
                            radial-gradient(1100px 520px at 18% 12%, rgba(255,255,255,.20), transparent 55%),
                            linear-gradient(150deg, #8863E5 0%, #7a52e0 45%, #5c3cad 100%);
                    }
                    .velto-brand__grid {
                        position: absolute; inset: 0; z-index: -1; opacity: .10;
                        background-image:
                            linear-gradient(rgba(255,255,255,.7) 1px, transparent 1px),
                            linear-gradient(90deg, rgba(255,255,255,.7) 1px, transparent 1px);
                        background-size: 46px 46px;
                        -webkit-mask-image: radial-gradient(circle at 28% 32%, #000, transparent 72%);
                        mask-image: radial-gradient(circle at 28% 32%, #000, transparent 72%);
                    }
                    .velto-brand__spark { position: absolute; z-index: -1; }
                    .velto-brand__spark--1 { top: 11%; inset-inline-end: 13%; width: 120px; opacity: .5; }
                    .velto-brand__spark--2 { bottom: 15%; inset-inline-start: 9%; width: 66px; opacity: .32; }
                    .velto-brand__logo {
                        height: 42px; width: auto; align-self: flex-start;
                        filter: brightness(0) invert(1); /* purple wordmark -> white */
                    }
                    .velto-brand__badge {
                        display: inline-flex; align-items: center; gap: .5rem;
                        align-self: flex-start;
                        padding: .4rem .9rem; border-radius: 999px;
                        background: rgba(255,255,255,.14);
                        border: 1px solid rgba(255,255,255,.24);
                        font-size: .78rem; font-weight: 500; letter-spacing: .02em;
                    }
                    .velto-brand__badge::before {
                        content: ''; width: .5rem; height: .5rem; border-radius: 999px;
                        background: #C9E3DA; box-shadow: 0 0 0 3px rgba(201,227,218,.25);
                    }
                    .velto-brand__title {
                        margin: 0; max-width: 20ch;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 700; font-size: clamp(1.7rem, 2.6vw, 2.25rem); line-height: 1.15;
                    }
                    .velto-brand__subtitle {
                        margin: 0; max-width: 42ch;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 400; font-size: 1rem; line-height: 1.65;
                        color: rgba(255,255,255,.82);
                    }

                    /* Language switcher (AR/EN) — pin top-right of the auth screen.
                       The package wrapper is w-fit + fixed with no inline offset,
                       so it defaults to the top-left; anchor it to the end side. */
                    .fls-display-on.fixed {
                        inset-inline-start: auto;
                        inset-inline-end: 0;
                        padding: 1.15rem 1.35rem;
                    }
                    /* The package trigger's Tailwind utilities aren't compiled into
                       the panel CSS, so style the pill explicitly (theme-aware). */
                    .fls-display-on > div { background: transparent !important; }
                    .fls-display-on .language-switch-trigger {
                        width: auto;
                        height: 2.4rem;
                        min-width: 2.4rem;
                        padding: 0 .95rem;
                        border-radius: 999px;
                        font-weight: 600;
                        font-size: .9rem;
                        letter-spacing: .01em;
                        background: rgba(136, 99, 229, .12);
                        color: #6d4fd0;
                        border: 1px solid rgba(136, 99, 229, .28);
                        box-shadow: 0 8px 18px -10px rgba(88, 99, 229, .55);
                        transition: background .15s ease, border-color .15s ease;
                    }
                    .fls-display-on .language-switch-trigger:hover {
                        background: rgba(136, 99, 229, .2);
                        border-color: rgba(136, 99, 229, .45);
                    }
                    @media (prefers-color-scheme: dark) {
                        .fls-display-on .language-switch-trigger {
                            background: rgba(136, 99, 229, .2);
                            color: #cbb8f6;
                            border-color: rgba(136, 99, 229, .4);
                        }
                    }
                    :is(.dark, [data-theme="dark"]) .fls-display-on .language-switch-trigger {
                        background: rgba(136, 99, 229, .2);
                        color: #cbb8f6;
                        border-color: rgba(136, 99, 229, .4);
                    }

                    /* Small screens: drop the brand pane, keep a soft tinted canvas */
                    @media (max-width: 1023px) {
                        .velto-brand { display: none; }
                        .fi-simple-layout {
                            background: radial-gradient(820px 420px at 50% -12%, rgba(136,99,229,.16), transparent 62%);
                        }
                    }
                </style>

                <aside class="velto-brand" aria-hidden="true">
                    <div class="velto-brand__grid"></div>
                    <svg class="velto-brand__spark velto-brand__spark--1" viewBox="0 0 100 100" fill="none">
                        <path d="M50 0C52 27 73 48 100 50C73 52 52 73 50 100C48 73 27 52 0 50C27 48 48 27 50 0Z" fill="#fff"/>
                    </svg>
                    <svg class="velto-brand__spark velto-brand__spark--2" viewBox="0 0 100 100" fill="none">
                        <path d="M50 0C52 27 73 48 100 50C73 52 52 73 50 100C48 73 27 52 0 50C27 48 48 27 50 0Z" fill="#fff"/>
                    </svg>
                    <img src="/img/logo-velto.png" alt="Velto" class="velto-brand__logo">
                    <span class="velto-brand__badge">Admin Console</span>
                    <h1 class="velto-brand__title">Mobile car&#8209;care, managed with care.</h1>
                    <p class="velto-brand__subtitle">Sign in to manage bookings, customers, workers, and daily operations across the Velto platform.</p>
                </aside>
            HTML,
        );
    }
}
