<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleName = $user->role?->name;

        [$dashboardTitle, $dashboardDescription, $shortcuts] = match ($roleName) {
            'admin_kecamatan' => [
                'Dashboard Admin Kecamatan',
                'Akses penuh untuk manajemen wilayah kecamatan, desa, pelanggan, tagihan, pembayaran, dan manajemen role pengguna.',
                [
                    ['label' => 'Master Kecamatan', 'route' => 'kecamatan.index'],
                    ['label' => 'Master Desa', 'route' => 'desa.index'],
                    ['label' => 'Data Pelanggan', 'route' => 'pelanggan.index'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring Map', 'route' => 'monitoring.index'],
                    ['label' => 'Manajemen Role', 'route' => 'admin.users'],
                ],
            ],
            'admin_desa' => [
                'Dashboard Admin Desa',
                'Akses operasional untuk data desa, pelanggan, proses tagihan, dan monitoring pembayaran.',
                [
                    ['label' => 'Master Desa', 'route' => 'desa.index'],
                    ['label' => 'Data Pelanggan', 'route' => 'pelanggan.index'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring Map', 'route' => 'monitoring.index'],
                ],
            ],
            'petugas_lapangan' => [
                'Dashboard Petugas Lapangan',
                'Akses untuk pencatatan meter pelanggan, validasi pembayaran lapangan, dan pemantauan gangguan.',
                [
                    ['label' => 'Pencatatan Meter', 'route' => 'meter_records.index'],
                    ['label' => 'Input Meter', 'route' => 'meter_records.create'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Input Pembayaran', 'route' => 'pembayaran.create'],
                    ['label' => 'Monitoring Map', 'route' => 'monitoring.index'],
                ],
            ],
            default => [
                'Dashboard Sistem Air Bersih',
                'Dashboard ringkas operasional untuk mengelola data master, pelanggan, tagihan, pembayaran, dan monitoring.',
                [
                    ['label' => 'Master Kecamatan', 'route' => 'kecamatan.index'],
                    ['label' => 'Master Desa', 'route' => 'desa.index'],
                    ['label' => 'Data Pelanggan', 'route' => 'pelanggan.index'],
                    ['label' => 'Pencatatan Meter', 'route' => 'meter_records.index'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring Map', 'route' => 'monitoring.index'],
                ],
            ],
        };

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'dashboardTitle' => $dashboardTitle,
            'dashboardDescription' => $dashboardDescription,
            'shortcuts' => $shortcuts,
            'totalPelanggan' => Pelanggan::count(),
            'totalTagihan' => Tagihan::count(),
            'totalPembayaran' => Pembayaran::count(),
            'totalTunggakan' => Tagihan::where('status', 'menunggak')->count(),
            'totalGangguan' => ActivityLog::where(function ($query) {
                $query->where('action', 'like', '%gangguan%')
                    ->orWhere('description', 'like', '%gangguan%')
                    ->orWhere('action', 'like', '%anomali%');
            })->count(),
        ]);
    }
}
