<?php

use App\Models\AppSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
