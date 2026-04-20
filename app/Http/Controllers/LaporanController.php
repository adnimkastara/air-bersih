<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\DistrictBilling;
use App\Models\LaporanGangguan;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->applyRoleFilter($request, $this->validateFilters($request));
        $reports = $this->buildReports($filters);

        return view('laporan.index', [
            'filters' => $filters,
            'desas' => Desa::orderBy('name')->get(),
            'reports' => $reports,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->applyRoleFilter($request, $this->validateFilters($request));
        $report = $request->validate([
            'report' => ['required', 'in:pelanggan,tagihan,pembayaran,tunggakan,gangguan,keuangan,setoran_kecamatan'],
        ])['report'];

        $reports = $this->buildReports($filters);

        $filename = sprintf('laporan-%s-%s.xls', $report, now()->format('YmdHis'));

        return response()->view('laporan.exports.excel', [
            'report' => $report,
            'rows' => $reports[$report],
            'filters' => $filters,
        ])->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->applyRoleFilter($request, $this->validateFilters($request));
        $report = $request->validate([
            'report' => ['required', 'in:pelanggan,tagihan,pembayaran,tunggakan,gangguan,keuangan,setoran_kecamatan'],
        ])['report'];

        $reports = $this->buildReports($filters);
        $setting = AppSetting::resolveForUser($request->user());

        $labels = [
            'pelanggan' => 'Laporan Pelanggan',
            'tagihan' => 'Laporan Tagihan',
            'pembayaran' => 'Laporan Pembayaran',
            'tunggakan' => 'Laporan Tunggakan',
            'gangguan' => 'Laporan Gangguan',
            'keuangan' => 'Laporan Keuangan Sederhana',
            'setoran_kecamatan' => 'Laporan Setoran Desa ke Kecamatan',
        ];

        return response()->view('laporan.exports.pdf', [
            'report' => $report,
            'title' => $labels[$report],
            'rows' => $reports[$report],
            'filters' => $filters,
            'setting' => $setting,
            'printedAt' => now(),
        ]);
    }



    private function applyRoleFilter(Request $request, array $filters): array
    {
        if ($request->user()->isRoot()) {
            return $filters;
        }

        $filters['desa_id'] = $request->user()->desa_id;

        return $filters;
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'desa_id' => ['nullable', 'integer', 'exists:desas,id'],
        ]);
    }

    private function buildReports(array $filters): array
    {
        $pelangganRows = Pelanggan::query()
            ->with('desa')
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->where('desa_id', $desaId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderBy('name')
            ->get()
            ->map(fn (Pelanggan $item) => [
                'kode_pelanggan' => $item->kode_pelanggan,
                'nama' => $item->name,
                'desa' => $item->desa?->name,
                'status' => $item->status,
                'dibuat_pada' => $item->created_at?->format('Y-m-d H:i'),
            ])
            ->all();

        $tagihanRows = Tagihan::query()
            ->with(['pelanggan.desa'])
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->whereHas('pelanggan', fn (Builder $builder) => $builder->where('desa_id', $desaId)))
            ->when($filters['date_from'] ?? null, function (Builder $query, $dateFrom) {
                $query->where(function (Builder $inner) use ($dateFrom) {
                    $inner->whereDate('due_date', '>=', $dateFrom)
                        ->orWhereDate('created_at', '>=', $dateFrom);
                });
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, $dateTo) {
                $query->where(function (Builder $inner) use ($dateTo) {
                    $inner->whereDate('due_date', '<=', $dateTo)
                        ->orWhereDate('created_at', '<=', $dateTo);
                });
            })
            ->orderByDesc('due_date')
            ->get()
            ->map(fn (Tagihan $item) => [
                'id_tagihan' => $item->id,
                'pelanggan' => $item->pelanggan?->name,
                'desa' => $item->pelanggan?->desa?->name,
                'periode' => $item->period,
                'jumlah' => (float) $item->amount,
                'status' => $item->status,
                'jatuh_tempo' => $item->due_date?->format('Y-m-d'),
            ])
            ->all();

        $pembayaranRows = Pembayaran::query()
            ->with(['tagihan.pelanggan.desa', 'petugas'])
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->whereHas('tagihan.pelanggan', fn (Builder $builder) => $builder->where('desa_id', $desaId)))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('paid_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('paid_at', '<=', $dateTo))
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (Pembayaran $item) => [
                'id_pembayaran' => $item->id,
                'tagihan_id' => $item->tagihan_id,
                'pelanggan' => $item->tagihan?->pelanggan?->name,
                'desa' => $item->tagihan?->pelanggan?->desa?->name,
                'metode' => str($item->payment_method)->replace('_', ' ')->title(),
                'jumlah' => (float) $item->amount,
                'tanggal_bayar' => $item->paid_at?->format('Y-m-d'),
                'petugas' => $item->petugas?->name,
            ])
            ->all();

        $today = Carbon::today()->toDateString();
        $tunggakanRows = Tagihan::query()
            ->with(['pelanggan.desa'])
            ->where(function (Builder $query) use ($today) {
                $query->where('status', 'menunggak')
                    ->orWhere(function (Builder $inner) use ($today) {
                        $inner->where('status', '!=', 'lunas')
                            ->whereDate('due_date', '<', $today);
                    });
            })
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->whereHas('pelanggan', fn (Builder $builder) => $builder->where('desa_id', $desaId)))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('due_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('due_date', '<=', $dateTo))
            ->orderBy('due_date')
            ->get()
            ->map(fn (Tagihan $item) => [
                'id_tagihan' => $item->id,
                'pelanggan' => $item->pelanggan?->name,
                'desa' => $item->pelanggan?->desa?->name,
                'periode' => $item->period,
                'jumlah_tunggakan' => (float) $item->amount,
                'status' => $item->status,
                'jatuh_tempo' => $item->due_date?->format('Y-m-d'),
            ])
            ->all();

        $gangguanRows = LaporanGangguan::query()
            ->with(['pelanggan.desa', 'reporter'])
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->whereHas('pelanggan', fn (Builder $builder) => $builder->where('desa_id', $desaId)))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('reported_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('reported_at', '<=', $dateTo))
            ->orderByDesc('reported_at')
            ->get()
            ->map(fn (LaporanGangguan $item) => [
                'id_laporan' => $item->id,
                'pelanggan' => $item->pelanggan?->name,
                'desa' => $item->pelanggan?->desa?->name,
                'jenis' => $item->jenis_laporan,
                'judul' => $item->judul,
                'status' => $item->status_penanganan,
                'dilaporkan_pada' => $item->reported_at?->format('Y-m-d H:i'),
                'pelapor' => $item->reporter?->name,
            ])
            ->all();

        $keuanganRows = Desa::query()
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->where('id', $desaId))
            ->with(['pelanggans.tagihans', 'pelanggans.tagihans.pembayarans'])
            ->orderBy('name')
            ->get()
            ->map(function (Desa $desa) use ($filters) {
                $tagihans = $desa->pelanggans->flatMap->tagihans;

                if (! empty($filters['date_from'])) {
                    $tagihans = $tagihans->filter(function (Tagihan $tagihan) use ($filters) {
                        return ($tagihan->due_date?->toDateString() ?? $tagihan->created_at->toDateString()) >= $filters['date_from'];
                    });
                }

                if (! empty($filters['date_to'])) {
                    $tagihans = $tagihans->filter(function (Tagihan $tagihan) use ($filters) {
                        return ($tagihan->due_date?->toDateString() ?? $tagihan->created_at->toDateString()) <= $filters['date_to'];
                    });
                }

                $totalTagihan = $tagihans->sum('amount');
                $totalPembayaran = $tagihans->flatMap->pembayarans->sum('amount');
                $totalTunggakan = $tagihans->whereIn('status', ['menunggak', 'terbit', 'draft'])->sum('amount');

                return [
                    'desa' => $desa->name,
                    'jumlah_pelanggan' => $desa->pelanggans->count(),
                    'total_tagihan' => (float) $totalTagihan,
                    'total_pembayaran' => (float) $totalPembayaran,
                    'total_tunggakan' => (float) $totalTunggakan,
                    'selisih' => (float) ($totalTagihan - $totalPembayaran),
                ];
            })
            ->all();


        $setoranKecamatanRows = DistrictBilling::query()
            ->with('desa')
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->where('desa_id', $desaId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('period')
            ->get()
            ->map(fn (DistrictBilling $item) => [
                'desa' => $item->desa?->name,
                'periode' => $item->period,
                'total_pemakaian_m3' => (int) $item->total_usage_m3,
                'tarif_kecamatan_per_m3' => (float) $item->tarif_per_m3,
                'total_setoran' => (float) $item->total_setoran,
                'status_tagihan' => $item->status,
                'status_pembayaran' => $item->payment_status,
                'total_pembayaran' => (float) $item->paid_amount,
                'sisa_tunggakan' => (float) ($item->total_setoran - $item->paid_amount),
                'tanggal_bayar' => $item->paid_at?->format('Y-m-d'),
                'metode_bayar' => $item->payment_method,
                'jatuh_tempo' => $item->due_date?->format('Y-m-d'),
            ])
            ->all();

        return [
            'pelanggan' => $pelangganRows,
            'tagihan' => $tagihanRows,
            'pembayaran' => $pembayaranRows,
            'tunggakan' => $tunggakanRows,
            'gangguan' => $gangguanRows,
            'keuangan' => $keuanganRows,
            'setoran_kecamatan' => $setoranKecamatanRows,
        ];
    }
}
