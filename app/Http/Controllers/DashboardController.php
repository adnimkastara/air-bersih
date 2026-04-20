<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Desa;
use App\Models\DistrictBilling;
use App\Models\LaporanGangguan;
use App\Models\MeterRecord;
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
                'Akses operasional level desa: pelanggan rumah tangga, tagihan dan pembayaran rumah tangga di desa Anda.',
                [
                    ['label' => 'Data Pelanggan', 'route' => 'pelanggan.index'],
                    ['label' => 'Tagihan Rumah Tangga', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran Rumah Tangga', 'route' => 'pembayaran.index'],
                    ['label' => 'Laporan Desa', 'route' => 'laporan.index'],
                ],
            ],
            'petugas_lapangan' => [
                'Dashboard Petugas Lapangan',
                'Akses operasional lapangan untuk pencatatan meter, tagihan rumah tangga, pembayaran rumah tangga, dan monitoring desa tugas.',
                [
                    ['label' => 'Pencatatan Meter', 'route' => 'meter_records.create'],
                    ['label' => 'Tagihan', 'route' => 'tagihan.index'],
                    ['label' => 'Pembayaran', 'route' => 'pembayaran.index'],
                    ['label' => 'Monitoring', 'route' => 'monitoring.index'],
                ],
            ],
            'admin_kecamatan' => [
                'Dashboard Admin Kecamatan',
                'Ringkasan lintas desa untuk operasional level kecamatan.',
                [
                    ['label' => 'Master Kecamatan', 'route' => 'kecamatan.index'],
                    ['label' => 'Master Desa', 'route' => 'desa.index'],
                    ['label' => 'Tagihan Kecamatan', 'route' => 'district-billings.index'],
                    ['label' => 'Pembayaran Kecamatan', 'route' => 'district-billings.payments'],
                    ['label' => 'Laporan Kecamatan', 'route' => 'laporan.index'],
                    ['label' => 'User Management', 'route' => 'settings.users.index'],
                ],
            ],
            default => [
                'Dashboard Kecamatan (Root)',
                'Ringkasan lintas desa berbasis agregasi per desa. Tidak menampilkan pelanggan rumah tangga sebagai unit utama.',
                [
                    ['label' => 'Master Kecamatan', 'route' => 'kecamatan.index'],
                    ['label' => 'Master Desa', 'route' => 'desa.index'],
                    ['label' => 'Tagihan Kecamatan', 'route' => 'district-billings.index'],
                    ['label' => 'Pembayaran Kecamatan', 'route' => 'district-billings.payments'],
                    ['label' => 'Laporan Kecamatan', 'route' => 'laporan.index'],
                    ['label' => 'User Management', 'route' => 'settings.users.index'],
                ],
            ],
        };

        $identitySetting = AppSetting::resolveForUser($user);
        $namaUnitPengelola = $identitySetting?->nama_unit_pengelola;
        $namaKecamatan = $identitySetting?->nama_kecamatan;

        if ($user->isKecamatanLevel()) {
            return $this->buildKecamatanDashboard($user, $dashboardTitle, $dashboardDescription, $shortcuts, $namaUnitPengelola, $namaKecamatan);
        }

        return $this->buildDesaDashboard($request, $user, $dashboardTitle, $dashboardDescription, $shortcuts, $namaUnitPengelola, $namaKecamatan);
    }

    private function buildKecamatanDashboard($user, string $dashboardTitle, string $dashboardDescription, array $shortcuts, ?string $namaUnitPengelola, ?string $namaKecamatan)
    {
        $period = now()->format('Y-m');

        $pelangganPerDesa = Pelanggan::query()
            ->selectRaw('desa_id, COUNT(*) as total')
            ->groupBy('desa_id')
            ->pluck('total', 'desa_id');

        $pemakaianPerDesa = MeterRecord::query()
            ->selectRaw('pelanggans.desa_id as desa_id, SUM(GREATEST(meter_records.meter_current_month - meter_records.meter_previous_month, 0)) as total_usage')
            ->join('pelanggans', 'pelanggans.id', '=', 'meter_records.pelanggan_id')
            ->groupBy('pelanggans.desa_id')
            ->pluck('total_usage', 'desa_id');

        $tagihanRtPerDesa = Tagihan::query()
            ->selectRaw('pelanggans.desa_id as desa_id, SUM(tagihans.amount) as total_tagihan')
            ->join('pelanggans', 'pelanggans.id', '=', 'tagihans.pelanggan_id')
            ->groupBy('pelanggans.desa_id')
            ->pluck('total_tagihan', 'desa_id');

        $pembayaranRtPerDesa = Pembayaran::query()
            ->selectRaw('pelanggans.desa_id as desa_id, SUM(pembayarans.amount) as total_pembayaran')
            ->join('tagihans', 'tagihans.id', '=', 'pembayarans.tagihan_id')
            ->join('pelanggans', 'pelanggans.id', '=', 'tagihans.pelanggan_id')
            ->groupBy('pelanggans.desa_id')
            ->pluck('total_pembayaran', 'desa_id');

        $districtBillsByDesa = DistrictBilling::query()->where('period', $period)->get()->keyBy('desa_id');

        $villageSummaries = Desa::query()->orderBy('name')->get()->map(function (Desa $desa) use ($pelangganPerDesa, $pemakaianPerDesa, $tagihanRtPerDesa, $pembayaranRtPerDesa, $districtBillsByDesa) {
            $districtBill = $districtBillsByDesa->get($desa->id);

            return [
                'desa_id' => $desa->id,
                'desa' => $desa->name,
                'jumlah_pelanggan' => (int) ($pelangganPerDesa[$desa->id] ?? 0),
                'total_pemakaian_m3' => (int) ($pemakaianPerDesa[$desa->id] ?? 0),
                'total_tagihan_rumah_tangga' => (float) ($tagihanRtPerDesa[$desa->id] ?? 0),
                'total_pembayaran_rumah_tangga' => (float) ($pembayaranRtPerDesa[$desa->id] ?? 0),
                'total_tagihan_kecamatan' => (float) ($districtBill?->total_setoran ?? 0),
                'total_pembayaran_kecamatan' => (float) ($districtBill?->paid_amount ?? 0),
                'status_setoran' => $districtBill?->payment_status ?? 'belum_bayar',
            ];
        });
        $districtBillings = DistrictBilling::query()->where('period', $period)->get();
        $keluhanQuery = LaporanGangguan::query()->latest('reported_at')->latest('id');
        if ($user->kecamatan_id) {
            $keluhanQuery->where('kecamatan_id', $user->kecamatan_id);
        }
        $recentKeluhan = $keluhanQuery->limit(5)->get();

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'dashboardTitle' => $dashboardTitle,
            'dashboardDescription' => $dashboardDescription,
            'shortcuts' => $shortcuts,
            'namaUnitPengelola' => $namaUnitPengelola,
            'namaKecamatan' => $namaKecamatan,
            'isKecamatanDashboard' => true,
            'selectedPeriod' => $period,
            'jumlahDesaAktif' => $villageSummaries->count(),
            'totalPelanggan' => (int) $villageSummaries->sum('jumlah_pelanggan'),
            'totalPemakaianM3' => (int) $villageSummaries->sum('total_pemakaian_m3'),
            'totalTagihan' => (float) $districtBillings->sum('total_setoran'),
            'totalPembayaran' => (float) $districtBillings->sum('paid_amount'),
            'totalTunggakan' => (float) ($districtBillings->sum('total_setoran') - $districtBillings->sum('paid_amount')),
            'totalGangguan' => LaporanGangguan::whereIn('status_penanganan', ['baru', 'diproses'])
                ->when($user->kecamatan_id, fn ($q) => $q->where('kecamatan_id', $user->kecamatan_id))
                ->count(),
            'recentKeluhan' => $recentKeluhan,
            'latestNotifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
            'villageSummaries' => $villageSummaries,
            'chartData' => $villageSummaries->take(6)->map(fn ($item) => ['label' => str($item['desa'])->limit(10, ''), 'value' => min(100, max(10, (int) round($item['total_tagihan_kecamatan'] > 0 ? ($item['total_pembayaran_kecamatan'] / $item['total_tagihan_kecamatan']) * 100 : 10)))])->values()->all(),
        ]);
    }

    private function buildDesaDashboard(Request $request, $user, string $dashboardTitle, string $dashboardDescription, array $shortcuts, ?string $namaUnitPengelola, ?string $namaKecamatan)
    {
        $pelangganQuery = Pelanggan::query()->where('desa_id', $user->desa_id);
        $tagihanQuery = Tagihan::query()->whereHas('pelanggan', fn ($query) => $query->where('desa_id', $user->desa_id));
        $pembayaranQuery = Pembayaran::query()->whereHas('tagihan.pelanggan', fn ($query) => $query->where('desa_id', $user->desa_id));
        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'dashboardTitle' => $dashboardTitle,
            'dashboardDescription' => $dashboardDescription,
            'shortcuts' => $shortcuts,
            'totalPelanggan' => $pelangganQuery->count(),
            'totalTagihan' => (float) $tagihanQuery->sum('amount'),
            'totalPembayaran' => (float) $pembayaranQuery->sum('amount'),
            'totalTunggakan' => (float) (clone $tagihanQuery)->where('status', 'menunggak')->sum('amount'),
            'totalGangguan' => LaporanGangguan::query()
                ->where('desa_id', $user->desa_id)
                ->whereIn('status_penanganan', ['baru', 'diproses'])
                ->count(),
            'recentKeluhan' => LaporanGangguan::query()->where('desa_id', $user->desa_id)->latest('reported_at')->latest('id')->limit(5)->get(),
            'namaUnitPengelola' => $namaUnitPengelola,
            'namaKecamatan' => $namaKecamatan,
            'isKecamatanDashboard' => false,
            'jumlahDesaAktif' => null,
            'totalPemakaianM3' => null,
            'selectedPeriod' => now()->format('Y-m'),
            'villageSummaries' => collect(),
            'chartData' => [
                ['label' => 'Jan', 'value' => 42],
                ['label' => 'Feb', 'value' => 55],
                ['label' => 'Mar', 'value' => 61],
                ['label' => 'Apr', 'value' => 49],
                ['label' => 'Mei', 'value' => 74],
                ['label' => 'Jun', 'value' => 82],
            ],
            'latestNotifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
        ]);
    }
}
