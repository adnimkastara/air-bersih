<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Desa;
use App\Models\Pelanggan;
use App\Services\GenerateCustomerCodeService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pelanggan:backfill-kode', function (GenerateCustomerCodeService $service) {
    $totalUpdated = 0;

    Desa::query()->orderBy('id')->chunkById(50, function ($desas) use ($service, &$totalUpdated) {
        foreach ($desas as $desa) {
            Pelanggan::query()
                ->where('desa_id', $desa->id)
                ->whereNull('kode_pelanggan')
                ->orderBy('created_at')
                ->orderBy('id')
                ->chunkById(100, function ($pelanggans) use ($desa, $service, &$totalUpdated) {
                    foreach ($pelanggans as $pelanggan) {
                        DB::transaction(function () use ($desa, $service, $pelanggan, &$totalUpdated) {
                            $desaLocked = Desa::query()->lockForUpdate()->findOrFail($desa->id);
                            $generated = $service->nextForDesa($desaLocked);

                            $pelanggan->update([
                                'kode_pelanggan' => $generated['kode_pelanggan'],
                                'nomor_urut_desa' => $generated['nomor_urut_desa'],
                            ]);

                            $totalUpdated++;
                        });
                    }
                });
        }
    });

    $this->info("Backfill selesai. {$totalUpdated} pelanggan diperbarui.");
})->purpose('Backfill kode_pelanggan untuk data lama yang belum memiliki kode.');
