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
            'email' => 'admin@airbersih.com',
        ], [
            'name' => 'System Admin',
            'password' => Hash::make('Admin1234!'),
            'role_id' => $adminRole->id,
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

        $petugas = User::firstOrCreate([
            'email' => 'petugas@airbersih.com',
        ], [
            'name' => 'Petugas Lapangan',
            'password' => Hash::make('Petugas123!'),
            'role_id' => $petugasRole->id,
        ]);

        User::firstOrCreate([
            'email' => 'user@airbersih.com',
        ], [
            'name' => 'User Umum',
            'password' => Hash::make('User12345!'),
            'role_id' => $userRole->id,
        ]);

        $kecamatan = \App\Models\Kecamatan::firstOrCreate(['name' => 'Kecamatan Utama']);
        $desa = \App\Models\Desa::firstOrCreate(['name' => 'Desa Satu', 'kecamatan_id' => $kecamatan->id]);

        $pelanggan = \App\Models\Pelanggan::firstOrCreate([
            'kode_pelanggan' => 'PLG-0001',
        ], [
            'name' => 'Pelanggan Satu',
            'email' => 'pelanggan1@example.com',
            'phone' => '08123456789',
            'address' => 'Jalan Contoh No.1',
            'dusun' => 'Dusun Melati',
            'jenis_pelanggan' => 'rumah_tangga',
            'nomor_meter' => 'MTR-10001',
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'status' => 'aktif',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'assigned_petugas_id' => $petugas->id,
        ]);

        \App\Models\Pelanggan::firstOrCreate([
            'kode_pelanggan' => 'PLG-0002',
        ], [
            'name' => 'Pelanggan Dua',
            'email' => 'pelanggan2@example.com',
            'phone' => '08139876543',
            'address' => 'Jalan Contoh No.2',
            'dusun' => 'Dusun Anggrek',
            'jenis_pelanggan' => 'niaga',
            'nomor_meter' => 'MTR-10002',
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'status' => 'nonaktif',
            'latitude' => -6.2015000,
            'longitude' => 106.8185000,
            'assigned_petugas_id' => $petugas->id,
        ]);

        $meterRecord = \App\Models\MeterRecord::firstOrCreate([
            'pelanggan_id' => $pelanggan->id,
            'recorded_at' => Carbon::now()->startOfMonth(),
        ], [
            'petugas_id' => $petugas->id,
            'meter_previous_month' => 100,
            'meter_current_month' => 150,
            'notes' => 'Catatan contoh',
        ]);

        $tagihanTerbit = \App\Models\Tagihan::firstOrCreate([
            'pelanggan_id' => $pelanggan->id,
            'period' => Carbon::now()->format('Y-m'),
        ], [
            'meter_record_id' => $meterRecord->id,
            'amount' => 75000,
            'status' => 'terbit',
            'due_date' => Carbon::now()->addDays(10)->toDateString(),
        ]);

        \App\Models\Tagihan::firstOrCreate([
            'pelanggan_id' => $pelanggan->id,
            'period' => Carbon::now()->subMonth()->format('Y-m'),
        ], [
            'meter_record_id' => $meterRecord->id,
            'amount' => 68000,
            'status' => 'menunggak',
            'due_date' => Carbon::now()->subDays(7)->toDateString(),
        ]);

        \App\Models\Pembayaran::firstOrCreate([
            'tagihan_id' => $tagihanTerbit->id,
            'paid_at' => Carbon::now()->toDateString(),
        ], [
            'petugas_id' => $petugas->id,
            'amount' => 75000,
            'notes' => 'Pembayaran contoh lunas di loket desa.',
        ]);

        \App\Models\ActivityLog::firstOrCreate([
            'action' => 'gangguan_pipa',
            'subject_type' => 'pelanggan',
            'subject_id' => $pelanggan->id,
        ], [
            'user_id' => $petugas->id,
            'description' => 'Laporan gangguan aliran air di RT 01.',
        ]);
    }
}
