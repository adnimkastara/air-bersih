<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FieldAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

    Route::middleware(['auth:web', 'role:root,admin_kecamatan,admin_desa,petugas_lapangan'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/pelanggan', [FieldAppController::class, 'pelangganIndex']);
        Route::get('/pelanggan/{pelanggan}', [FieldAppController::class, 'pelangganShow']);
        Route::get('/meter-records', [FieldAppController::class, 'meterIndex']);
        Route::post('/meter-records', [FieldAppController::class, 'meterStore']);
        Route::get('/tagihan', [FieldAppController::class, 'tagihanIndex']);
        Route::post('/pembayaran', [FieldAppController::class, 'pembayaranStore'])->middleware('role:admin_desa,petugas_lapangan');
        Route::get('/keluhan', [FieldAppController::class, 'keluhanIndex']);
        Route::post('/keluhan', [FieldAppController::class, 'keluhanStore']);
        Route::get('/dashboard-ringkas', [FieldAppController::class, 'dashboardRingkas']);
        Route::get('/monitoring/peta', [FieldAppController::class, 'monitoringPeta']);
    });
});
