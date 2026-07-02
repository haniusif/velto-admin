<?php

namespace App\Providers;

use App\Services\ARB\ArbCrypto;
use App\Services\ARB\ArbGateway;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
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
    }
}
