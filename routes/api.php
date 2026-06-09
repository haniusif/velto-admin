<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LocationsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\VehicleController;
use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);

    Route::prefix('locations')->group(function () {
        Route::get('/cities', [LocationsController::class, 'cities']);
        Route::get('/cities/{city}/areas', [LocationsController::class, 'areas']);
    });

    Route::prefix('catalog')->group(function () {
        Route::get('/vehicle-brands', [CatalogController::class, 'vehicleBrands']);
        Route::get('/vehicle-colors', [CatalogController::class, 'vehicleColors']);
        Route::get('/countries', [CatalogController::class, 'countries']);
        Route::get('/faqs', [CatalogController::class, 'faqs']);
        Route::get('/legal/{slug}', [CatalogController::class, 'legal']);
        Route::get('/support', [CatalogController::class, 'support']);
        Route::get('/wash-packages', [CatalogController::class, 'washPackages']);
        Route::get('/coverage/check', [CatalogController::class, 'coverageCheck']);
        Route::get('/availability', [CatalogController::class, 'availability']);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/request-otp', [AuthController::class, 'requestOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

        Route::middleware('auth:customer')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::patch('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/avatar', [AuthController::class, 'updateAvatar']);
        });
    });

    Route::middleware('auth:customer')->prefix('me')->group(function () {
        // Vehicles
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::patch('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
        Route::post('/vehicles/{vehicle}/default', [VehicleController::class, 'setDefault']);

        // Wallet
        Route::get('/wallet', [WalletController::class, 'show']);
        Route::post('/wallet/topup', [WalletController::class, 'topUp']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

        // Appointments / bookings
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    });
});
