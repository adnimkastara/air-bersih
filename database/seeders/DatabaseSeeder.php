<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Role::firstOrCreate(['name' => 'root']);
        Role::firstOrCreate(['name' => 'admin_desa']);
        Role::firstOrCreate(['name' => 'petugas_lapangan']);

        // Master wilayah selalu disiapkan, aman untuk production.
        $this->call(KaranganyarWilayahSeeder::class);

        // Data contoh operasional hanya untuk non-production atau jika dipaksa via env.
        $seedSampleData = (bool) env('SEED_SAMPLE_DATA', ! app()->environment('production'));

        if ($seedSampleData) {
            $this->call(SampleOperationalSeeder::class);
        }
    }
}
