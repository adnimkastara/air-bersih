<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Tarif;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $rootRole = \App\Models\Role::firstOrCreate(['name' => 'root']);
        $adminDesaRole = \App\Models\Role::firstOrCreate(['name' => 'admin_desa']);
        $petugasRole = \App\Models\Role::firstOrCreate(['name' => 'petugas_lapangan']);

        $kecamatan = Kecamatan::firstOrCreate(['name' => 'Karanganyar']);
        $desa = Desa::firstOrCreate([
            'name' => 'Ponjen',
            'kecamatan_id' => $kecamatan->id,
        ]);

        User::firstOrCreate(['email' => 'root@airbersih.com'], [
            'name' => 'Root Admin',
            'password' => Hash::make('Admin1234!'),
            'is_active' => true,
            'role_id' => $rootRole->id,
        ]);

        User::firstOrCreate(['email' => 'admin.desa@airbersih.com'], [
            'name' => 'Admin Desa Ponjen',
            'password' => Hash::make('Admin1234!'),
            'is_active' => true,
            'role_id' => $adminDesaRole->id,
            'desa_id' => $desa->id,
        ]);

        $petugas = User::firstOrCreate(['email' => 'pencatat.meter@airbersih.com'], [
            'name' => 'Pencatat Meter Desa Ponjen',
            'password' => Hash::make('Petugas123!'),
            'is_active' => true,
            'role_id' => $petugasRole->id,
            'desa_id' => $desa->id,
            'petugas_subtype' => 'pencatat_meter',
        ]);

        User::firstOrCreate(['email' => 'penagih.iuran@airbersih.com'], [
            'name' => 'Penagih Iuran Desa Ponjen',
            'password' => Hash::make('Petugas123!'),
            'is_active' => true,
            'role_id' => $petugasRole->id,
            'desa_id' => $desa->id,
            'petugas_subtype' => 'penagih_iuran',
        ]);

        AppSetting::updateOrCreate(
            ['scope_key' => AppSetting::scopeKeyForGlobal()],
            [
                'scope_type' => AppSetting::SCOPE_GLOBAL,
                'nama_kecamatan' => 'Karanganyar',
                'nama_unit_pengelola' => 'Pengelola Air Bersih Kecamatan Karanganyar',
                'tipe_pengelola' => 'BUMDES',
                'nama_aplikasi' => 'Air Bersih Desa',
            ]
        );

        AppSetting::updateOrCreate(
            ['scope_key' => AppSetting::scopeKeyForDesa($desa->id)],
            [
                'scope_type' => AppSetting::SCOPE_DESA,
                'desa_id' => $desa->id,
                'nama_unit_pengelola' => 'BUMDES Berkah Mulya Desa Ponjen',
                'tipe_pengelola' => 'BUMDES',
            ]
        );

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

        $tarifRumahTangga = Tarif::updateOrCreate([
            'name' => 'Tarif Rumah Tangga Desa Ponjen',
            'scope_type' => Tarif::SCOPE_DESA,
            'village_id' => $desa->id,
        ], [
            'customer_type' => 'rumah_tangga',
            'category' => 'rumah_tangga',
            'abonemen' => 5000,
            'tarif_dasar' => 10000,
            'tarif_per_m3' => 1500,
            'base_rate' => 10000,
            'usage_rate' => 1500,
            'late_fee_per_day' => 2000,
            'status' => 'aktif',
            'is_active' => true,
            'effective_start' => Carbon::now()->startOfYear()->toDateString(),
        ]);

        Tarif::updateOrCreate([
            'name' => 'Tarif Setoran Kecamatan',
            'scope_type' => Tarif::SCOPE_KECAMATAN,
        ], [
            'customer_type' => 'desa',
            'category' => 'desa',
            'abonemen' => 0,
            'tarif_dasar' => 0,
            'tarif_per_m3' => 600,
            'base_rate' => 0,
            'usage_rate' => 600,
            'late_fee_per_day' => 0,
            'status' => 'aktif',
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
            'amount' => 90000,
            'status' => 'terbit',
            'due_date' => Carbon::now()->addDays(10)->toDateString(),
            'usage_m3' => 50,
            'base_amount' => 15000,
            'usage_amount' => 75000,
            'late_fee' => 0,
            'generated_at' => now(),
        ]);

        \App\Models\Pembayaran::firstOrCreate([
            'tagihan_id' => $tagihanTerbit->id,
            'paid_at' => Carbon::now()->toDateString(),
        ], [
            'petugas_id' => $petugas->id,
            'amount' => 90000,
            'notes' => 'Pembayaran contoh lunas di loket desa.',
        ]);
    }
}
