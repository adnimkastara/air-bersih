<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tarif;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $rootRole = Role::firstOrCreate(['name' => 'root']);
        $adminDesaRole = Role::firstOrCreate(['name' => 'admin_desa']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas_lapangan']);

        $kecamatan = \App\Models\Kecamatan::firstOrCreate(['name' => 'Kecamatan Utama']);
        $desa = \App\Models\Desa::firstOrCreate([
            'name' => 'Desa Satu',
            'kecamatan_id' => $kecamatan->id,
        ]);

        User::firstOrCreate(['email' => 'root@airbersih.com'], [
            'name' => 'Root Admin',
            'password' => Hash::make('Admin1234!'),
            'role_id' => $rootRole->id,
        ]);

        User::firstOrCreate(['email' => 'admin.desa@airbersih.com'], [
            'name' => 'Admin Desa Satu',
            'password' => Hash::make('Admin1234!'),
            'role_id' => $adminDesaRole->id,
            'desa_id' => $desa->id,
        ]);

        $petugas = User::firstOrCreate(['email' => 'pencatat.meter@airbersih.com'], [
            'name' => 'Pencatat Meter Desa Satu',
            'password' => Hash::make('Petugas123!'),
            'role_id' => $petugasRole->id,
            'desa_id' => $desa->id,
            'petugas_subtype' => 'pencatat_meter',
        ]);

        User::firstOrCreate(['email' => 'penagih.iuran@airbersih.com'], [
            'name' => 'Penagih Iuran Desa Satu',
            'password' => Hash::make('Petugas123!'),
            'role_id' => $petugasRole->id,
            'desa_id' => $desa->id,
            'petugas_subtype' => 'penagih_iuran',
        ]);

        $pelanggan = \App\Models\Pelanggan::updateOrCreate([
            'email' => 'pelanggan1@example.com',
        ], [
            'kode_pelanggan' => 'PLG-0001',
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

        $tarifRumahTangga = Tarif::firstOrCreate([
            'name' => 'Tarif Rumah Tangga',
            'customer_type' => 'rumah_tangga',
        ], [
            'base_rate' => 10000,
            'usage_rate' => 1500,
            'late_fee_per_day' => 2000,
            'is_active' => true,
            'effective_start' => Carbon::now()->startOfYear()->toDateString(),
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
            'tarif_id' => $tarifRumahTangga->id,
            'amount' => 75000,
            'status' => 'terbit',
            'due_date' => Carbon::now()->addDays(10)->toDateString(),
            'usage_m3' => 50,
            'base_amount' => 10000,
            'usage_amount' => 65000,
            'late_fee' => 0,
            'generated_at' => now(),
        ]);

        \App\Models\Pembayaran::firstOrCreate([
            'tagihan_id' => $tagihanTerbit->id,
            'paid_at' => Carbon::now()->toDateString(),
        ], [
            'petugas_id' => $petugas->id,
            'amount' => 75000,
            'notes' => 'Pembayaran contoh lunas di loket desa.',
        ]);
    }
}
