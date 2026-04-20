<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KaranganyarWilayahSeeder extends Seeder
{
    public function run(): void
    {
        $kecamatan = Kecamatan::updateOrCreate(
            ['name' => 'Karanganyar'],
            ['name' => 'Karanganyar']
        );

        $desaNames = [
            'Ponjen',
            'Brakas',
            'Buara',
            'Lumpang',
            'Bungkanel',
            'Kabunderan',
            'Karanggedang',
            'Banjarkerta',
            'Maribaya',
            'Jambudesa',
            'Karanganyar',
            'Kalijaran',
            'Kaliori',
        ];

        foreach ($desaNames as $desaName) {
            Desa::updateOrCreate(
                ['kecamatan_id' => $kecamatan->id, 'name' => $desaName],
                ['kecamatan_id' => $kecamatan->id, 'name' => $desaName]
            );
        }
    }
}
