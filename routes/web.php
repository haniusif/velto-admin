<?php

use App\Http\Controllers\Site\SiteController;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/services', [SiteController::class, 'services'])->name('site.services');
Route::get('/plans', [SiteController::class, 'plans'])->name('site.plans');
Route::get('/faq', [SiteController::class, 'faq'])->name('site.faq');
Route::get('/coverage', [SiteController::class, 'coverage'])->name('site.coverage');
Route::get('/terms', fn () => app(SiteController::class)->legal('terms'))->name('site.terms');
Route::get('/privacy', fn () => app(SiteController::class)->legal('privacy'))->name('site.privacy');
Route::get('/login', [SiteController::class, 'login'])->name('site.login');
Route::get('/complete-profile', [SiteController::class, 'completeProfile'])->name('site.complete-profile');
Route::get('/book', [SiteController::class, 'book'])->name('site.book');
Route::get('/book/done', [SiteController::class, 'bookDone'])->name('site.book.done');
Route::get('/account/{section?}/{id?}', [SiteController::class, 'account'])
    ->where('section', 'bookings|vehicles|wallet|plans|profile|support|notifications')
    ->name('site.account');

/**
 * Public account-deletion page.
 *
 * Google Play's Data safety form requires a URL where someone can request
 * deletion WITHOUT installing the app, and the reviewer opens it — so it has to
 * be reachable with no auth. Contacts come from the support settings rather
 * than being hard-coded, so changing them in the admin changes this page too.
 */
Route::get('/delete-account', function () {
    $support = AppSetting::group('support');

    return view('delete-account', [
        'supportEmail' => $support['support.email_support']
            ?? $support['support.email_general']
            ?? 'support@velto.sa',
        'whatsapp' => $support['support.whatsapp'] ?? null,
        'phone' => $support['support.phone'] ?? null,
        'website' => $support['support.website_url'] ?? 'https://velto.sa',
        'websiteDisplay' => $support['support.website_display'] ?? 'velto.sa',
    ]);
})->name('delete-account');
