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
            'admin_desa' => [
                'Dashboard Admin Desa',
                'Akses operasional untuk data desa, pelanggan, proses tagihan, pembayaran, monitoring, laporan, dan manajemen petugas desa.',
                [
                    ['label' => 'Data Pelanggan', 'route' => 'pelanggan.index'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring', 'route' => 'monitoring.index'],
                    ['label' => 'Laporan Desa', 'route' => 'laporan.index'],
                    ['label' => 'User Petugas', 'route' => 'users.index'],
                ],
            ],
            'petugas_lapangan' => [
                'Dashboard Petugas Lapangan',
                'Akses untuk pencatatan meter, operasional tagihan, pembayaran, dan monitoring desa penugasan.',
                [
                    ['label' => 'Pencatatan', 'route' => 'meter_records.index'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Meter Record', 'route' => 'meter_records.create'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring', 'route' => 'monitoring.index'],
                ],
            ],
            default => [
                'Dashboard Root',
                'Akses penuh lintas desa untuk manajemen master, operasional, laporan, dan user management.',
                [
                    ['label' => 'Master Kecamatan', 'route' => 'kecamatan.index'],
                    ['label' => 'Master Desa', 'route' => 'desa.index'],
                    ['label' => 'Data Pelanggan', 'route' => 'pelanggan.index'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring', 'route' => 'monitoring.index'],
                    ['label' => 'Laporan', 'route' => 'laporan.index'],
                    ['label' => 'User Management', 'route' => 'users.index'],
                ],
            ],
        };

        $pelangganQuery = Pelanggan::query();
        $tagihanQuery = Tagihan::query();
        $pembayaranQuery = Pembayaran::query();
        $gangguanQuery = ActivityLog::where(function ($query) {
            $query->where('action', 'like', '%gangguan%')
                ->orWhere('description', 'like', '%gangguan%')
                ->orWhere('action', 'like', '%anomali%');
        });

        if (! $user->isRoot()) {
            $pelangganQuery->where('desa_id', $user->desa_id);
            $tagihanQuery->whereHas('pelanggan', fn ($query) => $query->where('desa_id', $user->desa_id));
            $pembayaranQuery->whereHas('tagihan.pelanggan', fn ($query) => $query->where('desa_id', $user->desa_id));
            $gangguanQuery->whereHas('user', fn ($query) => $query->where('desa_id', $user->desa_id));
        }

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'dashboardTitle' => $dashboardTitle,
            'dashboardDescription' => $dashboardDescription,
            'shortcuts' => $shortcuts,
            'totalPelanggan' => $pelangganQuery->count(),
            'totalTagihan' => $tagihanQuery->count(),
            'totalPembayaran' => $pembayaranQuery->count(),
            'totalTunggakan' => (clone $tagihanQuery)->where('status', 'menunggak')->count(),
            'totalGangguan' => $gangguanQuery->count(),
        ]);
    }
}
