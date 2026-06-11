<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InboundController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\MovingController;
use App\Http\Controllers\Api\OutboundController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WMS Lite API Routes
|--------------------------------------------------------------------------
| All routes are prefixed with /api (defined in bootstrap/app.php)
| Protected routes require: Authorization: Bearer <token>
*/

// ── Auth ──────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me',     [AuthController::class, 'me'])->name('auth.me');
    });
});

// ── Protected routes ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Shipments
    Route::post('shipments/{id}/push-inbound', [ShipmentController::class, 'pushInbound'])->name('shipments.push-inbound');
    Route::apiResource('shipments', ShipmentController::class);

    // Inbound
    Route::get('inbound/{id}/details', [InboundController::class, 'details'])->name('inbound.details');
    Route::apiResource('inbound', InboundController::class);

    // Outbound
    Route::apiResource('outbound', OutboundController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('shipment',  [ReportController::class, 'shipment'])->name('shipment');
        Route::get('inbound',   [ReportController::class, 'inbound'])->name('inbound');
        Route::get('outbound',  [ReportController::class, 'outbound'])->name('outbound');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
    });

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('locations',  [MasterController::class, 'locations'])->name('locations');
        Route::get('categories', [MasterController::class, 'categories'])->name('categories');
        Route::get('recipients', [MasterController::class, 'recipients'])->name('recipients');
    });

    // Moving (Stock Transfer)
    Route::get('moving',     [MovingController::class, 'index'])  ->name('moving.index');
    Route::post('moving',    [MovingController::class, 'store'])  ->name('moving.store');
    Route::delete('moving/{id}', [MovingController::class, 'destroy'])->name('moving.destroy');

    // Monitoring (Activity Log — read only)
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    // Admin — User Management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users',                          [UserController::class, 'index'])        ->name('users.index');
        Route::post('users',                         [UserController::class, 'store'])        ->name('users.store');
        Route::put('users/{id}',                     [UserController::class, 'update'])       ->name('users.update');
        Route::post('users/{id}/reset-password',     [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('users/{id}',                  [UserController::class, 'destroy'])      ->name('users.destroy');
    });
});
