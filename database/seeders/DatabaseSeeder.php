<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rootRole = Role::firstOrCreate(['name' => 'root']);
        $adminKecamatanRole = Role::firstOrCreate(['name' => 'admin_kecamatan']);
        $adminDesaRole = Role::firstOrCreate(['name' => 'admin_desa']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas_lapangan']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        User::firstOrCreate([
            'email' => 'root@airbersih.com',
        ], [
            'name' => 'Root Admin',
            'password' => Hash::make('Admin1234!'),
            'role_id' => $rootRole->id,
        ]);

        User::firstOrCreate([
            'email' => 'kecamatan@airbersih.com',
        ], [
            'name' => 'Admin Kecamatan',
            'password' => Hash::make('Admin1234!'),
            'role_id' => $adminKecamatanRole->id,
        ]);

        User::firstOrCreate([
            'email' => 'desa@airbersih.com',
        ], [
            'name' => 'Admin Desa',
            'password' => Hash::make('Admin1234!'),
            'role_id' => $adminDesaRole->id,
        ]);

        User::firstOrCreate([
            'email' => 'petugas@airbersih.com',
        ], [
            'name' => 'Petugas Lapangan',
            'password' => Hash::make('Petugas123!'),
            'role_id' => $petugasRole->id,
        ]);

        $kecamatan = \App\Models\Kecamatan::firstOrCreate(['name' => 'Kecamatan Utama']);
        $desa = \App\Models\Desa::firstOrCreate(['name' => 'Desa Satu', 'kecamatan_id' => $kecamatan->id]);

        \App\Models\Pelanggan::firstOrCreate([
            'email' => 'pelanggan1@example.com',
        ], [
            'name' => 'Pelanggan Satu',
            'phone' => '08123456789',
            'address' => 'Jalan Contoh No.1',
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'status' => 'aktif',
            'assigned_petugas_id' => User::where('email', 'petugas@airbersih.com')->first()->id,
        ]);

        $pelanggan = \App\Models\Pelanggan::first();
        \App\Models\MeterRecord::firstOrCreate([
            'pelanggan_id' => $pelanggan->id,
            'recorded_at' => Carbon::now(),
        ], [
            'petugas_id' => User::where('email', 'petugas@airbersih.com')->first()->id,
            'meter_previous_month' => 100,
            'meter_current_month' => 150,
            'notes' => 'Catatan contoh',
        ]);
    }
}
