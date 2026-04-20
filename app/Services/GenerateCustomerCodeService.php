<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Pelanggan;
use Illuminate\Validation\ValidationException;

class GenerateCustomerCodeService
{
    public function nextForDesa(Desa $desa): array
    {
        $kodeDesa = trim((string) $desa->kode_desa);

        if ($kodeDesa === '') {
            throw ValidationException::withMessages([
                'desa_id' => 'Kode desa belum diatur. Silakan lengkapi kode desa terlebih dahulu.',
            ]);
        }

        $lastNumber = (int) Pelanggan::query()
            ->where('desa_id', $desa->id)
            ->lockForUpdate()
            ->max('nomor_urut_desa');

        $nextNumber = $lastNumber + 1;

        return [
            'nomor_urut_desa' => $nextNumber,
            'kode_pelanggan' => sprintf('%s-%05d', $kodeDesa, $nextNumber),
        ];
    }
}
