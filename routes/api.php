<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FieldAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

    Route::middleware(['api.token', 'role:root,admin_kecamatan,admin_desa,petugas_lapangan'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile/password', [AuthController::class, 'updatePassword']);

        Route::get('/pelanggan', [FieldAppController::class, 'pelangganIndex']);
        Route::get('/pelanggan/{pelanggan}', [FieldAppController::class, 'pelangganShow']);
        Route::post('/pelanggan', [FieldAppController::class, 'pelangganStore'])->middleware('role:admin_kecamatan,admin_desa,petugas_lapangan');
        Route::match(['put', 'patch'], '/pelanggan/{pelanggan}', [FieldAppController::class, 'pelangganUpdate'])->middleware('role:admin_kecamatan,admin_desa,petugas_lapangan');
        Route::delete('/pelanggan/{pelanggan}', [FieldAppController::class, 'pelangganDestroy'])->middleware('role:admin_kecamatan,admin_desa');
        Route::get('/meter-records', [FieldAppController::class, 'meterIndex']);
        Route::post('/meter-records', [FieldAppController::class, 'meterStore']);
        Route::get('/tagihan', [FieldAppController::class, 'tagihanIndex']);
        Route::get('/tagihan/{tagihan}', [FieldAppController::class, 'tagihanShow']);
        Route::get('/tagihan-terbuka', [FieldAppController::class, 'tagihanOpen']);
        Route::post('/tagihan/generate', [FieldAppController::class, 'tagihanGenerate'])->middleware('role:admin_desa,petugas_lapangan');
        Route::post('/tagihan/{tagihan}/publish', [FieldAppController::class, 'tagihanPublish'])->middleware('role:admin_desa,petugas_lapangan');
        Route::get('/payment-methods', [FieldAppController::class, 'paymentMethods']);
        Route::get('/pembayaran', [FieldAppController::class, 'pembayaranIndex']);
        Route::post('/pembayaran', [FieldAppController::class, 'pembayaranStore'])->middleware('role:admin_desa,petugas_lapangan');
        Route::get('/pembayaran/{pembayaran}', [FieldAppController::class, 'pembayaranShow']);
        Route::get('/keluhan', [FieldAppController::class, 'keluhanIndex']);
        Route::post('/keluhan', [FieldAppController::class, 'keluhanStore']);
        Route::get('/dashboard-ringkas', [FieldAppController::class, 'dashboardRingkas']);
        Route::get('/monitoring/peta', [FieldAppController::class, 'monitoringPeta']);
    });
});
