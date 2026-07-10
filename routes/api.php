<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LocationsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\VehicleController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\WorkerAuthController;
use App\Http\Controllers\Api\V1\WorkerJobController;
use App\Http\Controllers\Api\V1\WorkerNotificationController;
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

    // Public ARB/Neoleap payment callbacks (called by the bank, not the app).
    Route::prefix('payments/arb')->group(function () {
        Route::match(['get', 'post'], '/callback', [PaymentController::class, 'callback']);
        Route::match(['get', 'post'], '/error', [PaymentController::class, 'error']);
        Route::post('/webhook', [PaymentController::class, 'webhook']);
        Route::get('/done', [PaymentController::class, 'done']);
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
        Route::post('/vehicles/photo', [VehicleController::class, 'photo']);
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
        Route::post('/appointments/{appointment}/pay', [AppointmentController::class, 'pay']);
        Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    });

    // --- Worker (staff) app -------------------------------------------------
    Route::prefix('worker')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/request-otp', [WorkerAuthController::class, 'requestOtp']);
            Route::post('/verify-otp', [WorkerAuthController::class, 'verifyOtp']);

            Route::middleware('auth:worker')->group(function () {
                Route::get('/me', [WorkerAuthController::class, 'me']);
                Route::post('/logout', [WorkerAuthController::class, 'logout']);
            });
        });

        Route::middleware('auth:worker')->group(function () {
            Route::get('/jobs', [WorkerJobController::class, 'index']);
            Route::get('/jobs/{appointment}', [WorkerJobController::class, 'show']);
            Route::post('/jobs/{appointment}/accept', [WorkerJobController::class, 'accept']);
            Route::post('/jobs/{appointment}/start', [WorkerJobController::class, 'start']);
            Route::post('/jobs/{appointment}/arrived', [WorkerJobController::class, 'arrived']);
            Route::post('/jobs/{appointment}/start-work', [WorkerJobController::class, 'startWork']);
            Route::post('/jobs/{appointment}/complete', [WorkerJobController::class, 'complete']);

            Route::get('/notifications', [WorkerNotificationController::class, 'index']);
            Route::post('/notifications/{notification}/read', [WorkerNotificationController::class, 'markRead']);
            Route::post('/notifications/read-all', [WorkerNotificationController::class, 'markAllRead']);
        });
    });
});
