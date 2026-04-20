<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MeterRecordController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\DistrictBillingController;
use App\Http\Controllers\Settings\TarifController;
use App\Http\Controllers\Settings\AppSettingController;
use App\Http\Controllers\Settings\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview', function () {
    return view('preview');
})->name('preview');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::middleware('role:root')->group(function () {
        Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    });

    Route::prefix('settings')->name('settings.')->middleware('role:root,admin_desa')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('/app', [AppSettingController::class, 'edit'])->name('app.edit');
        Route::put('/app', [AppSettingController::class, 'update'])->name('app.update');

        Route::get('/tarif', [TarifController::class, 'index'])->name('tarif.index');
        Route::post('/tarif/desa', [TarifController::class, 'storeDesa'])->name('tarif.store-desa');
    });

    Route::prefix('settings')->name('settings.')->middleware('role:root')->group(function () {
        Route::get('/tarif/kecamatan', [TarifController::class, 'kecamatan'])->name('tarif.kecamatan');
        Route::post('/tarif/kecamatan', [TarifController::class, 'storeKecamatan'])->name('tarif.store-kecamatan');
    });

    Route::middleware('role:root,admin_desa')->group(function () {
        Route::resource('desa', DesaController::class)->except(['show']);
    });

    
    Route::middleware('role:root,admin_desa')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    });


    Route::middleware('role:root,admin_desa')->group(function () {
        Route::get('/district-billings', [DistrictBillingController::class, 'index'])->name('district-billings.index');
        Route::get('/district-billings/payments', [DistrictBillingController::class, 'payments'])->name('district-billings.payments');
        Route::post('/district-billings/generate', [DistrictBillingController::class, 'generate'])->name('district-billings.generate');
        Route::post('/district-billings/{districtBilling}/payment', [DistrictBillingController::class, 'recordPayment'])->name('district-billings.record-payment');
    });

Route::middleware('role:root,admin_desa,petugas_lapangan')->group(function () {
        Route::resource('pelanggan', PelangganController::class);
        Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');
        Route::get('/tagihan/{tagihan}/print', [TagihanController::class, 'print'])->name('tagihan.print');
        Route::post('/tagihan/generate', [TagihanController::class, 'generate'])->name('tagihan.generate');
        Route::post('/tagihan/{tagihan}/publish', [TagihanController::class, 'publish'])->name('tagihan.publish');

        Route::get('/meter-records', [MeterRecordController::class, 'index'])->name('meter_records.index');
        Route::get('/meter-records/create', [MeterRecordController::class, 'create'])->name('meter_records.create');
        Route::post('/meter-records', [MeterRecordController::class, 'store'])->name('meter_records.store');

        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::get('/pembayaran/create', [PembayaranController::class, 'create'])->name('pembayaran.create');
        Route::post('/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
        Route::get('/pembayaran/{pembayaran}/receipt', [PembayaranController::class, 'receipt'])->name('pembayaran.receipt');

        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::post('/monitoring/laporan', [MonitoringController::class, 'store'])->name('monitoring.store');
    });
});
