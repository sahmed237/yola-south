<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EstablishmentController;
use App\Http\Controllers\Api\V1\SyncController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/establishments', [EstablishmentController::class, 'store']);
        Route::get('/establishments/{id}', [EstablishmentController::class, 'show']);
        Route::post('/sync', [SyncController::class, 'sync']);
        Route::get('/sync/status', [SyncController::class, 'fetchStatus']);
        Route::get('/sync/unpaid', [SyncController::class, 'fetchUnpaidTaxes']);
    });

    // For testing purposes without auth in MVP if needed
    Route::post('/public/establishments', [EstablishmentController::class, 'store']);
    Route::post('/public/sync', [SyncController::class, 'sync']);
});
