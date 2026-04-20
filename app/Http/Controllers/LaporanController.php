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
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    private const REPORT_LABELS = [
        'pelanggan' => 'Laporan Pelanggan',
        'tagihan' => 'Laporan Tagihan',
        'pembayaran' => 'Laporan Pembayaran',
        'tunggakan' => 'Laporan Tunggakan',
        'gangguan' => 'Laporan Gangguan',
        'keuangan' => 'Laporan Keuangan Sederhana',
        'setoran_kecamatan' => 'Laporan Setoran Desa ke Kecamatan',
    ];

    public function index(Request $request)
    {
        $availableReports = $this->availableReportKeys($request);
        $filters = $this->normalizeFilters(
            $this->applyRoleFilter($request, $this->validateFilters($request))
        );
        $reports = $this->buildReports($filters);

        return view('laporan.index', [
            'filters' => $filters,
            'desas' => Desa::orderBy('name')->get(),
            'reports' => $reports,
            'titles' => collect(self::REPORT_LABELS)->only($availableReports)->all(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $report = $request->validate(['report' => ['required', 'string']])['report'];
        $this->authorizeReportAccess($request, $report);
        $filters = $this->normalizeFilters(
            $this->applyRoleFilter($request, $this->validateFilters($request)),
            $report
        );

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
        $report = $request->validate(['report' => ['required', 'string']])['report'];
        $this->authorizeReportAccess($request, $report);
        $filters = $this->normalizeFilters(
            $this->applyRoleFilter($request, $this->validateFilters($request)),
            $report
        );

        $reports = $this->buildReports($filters);
        $setting = $this->resolveSettingForExport($request, $report, $filters);
        $exportMeta = $this->buildExportMeta($filters, $report, self::REPORT_LABELS[$report]);

        return response()->view('laporan.exports.pdf', [
            'report' => $report,
            'title' => self::REPORT_LABELS[$report],
            'rows' => $reports[$report],
            'filters' => $filters,
            'exportMeta' => $exportMeta,
            'setting' => $setting,
            'printedAt' => now(),
        ]);
    }

    private function availableReportKeys(Request $request): array
    {
        $reports = array_keys(self::REPORT_LABELS);

        if (! $request->user()->isRoot()) {
            return array_values(array_filter($reports, fn (string $report) => $report !== 'setoran_kecamatan'));
        }

        return $reports;
    }

    private function authorizeReportAccess(Request $request, string $report): void
    {
        if (! array_key_exists($report, self::REPORT_LABELS)) {
            abort(404);
        }

        if ($report === 'setoran_kecamatan' && ! $request->user()->isRoot()) {
            abort(403, 'Anda tidak memiliki akses ke laporan level kecamatan.');
        }
    }

    private function resolveSettingForExport(Request $request, string $report, array $filters): ?AppSetting
    {
        if ($request->user()->isRoot() && $report !== 'setoran_kecamatan' && ! empty($filters['desa_id'])) {
            $desaSetting = AppSetting::where('scope_key', AppSetting::scopeKeyForDesa((int) $filters['desa_id']))->first();

            if ($desaSetting) {
                return $desaSetting;
            }
        }

        return AppSetting::resolveForUser($request->user());
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

    private function normalizeFilters(array $filters, ?string $report = null): array
    {
        $normalized = [
            'report' => $report,
            'desa_id' => null,
            'date_from' => null,
            'date_to' => null,
            'period' => null,
            'status' => null,
        ];

        foreach ($normalized as $key => $defaultValue) {
            $normalized[$key] = $filters[$key] ?? $defaultValue;
        }

        return $normalized;
    }

    private function buildExportMeta(array $filters, string $report, string $title): array
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        return [
            'report_label' => $title,
            'report_type_label' => Str::of($report)->replace('_', ' ')->title()->toString(),
            'period_label' => $this->formatPeriodLabel($dateFrom, $dateTo),
            'desa_label' => $this->formatDesaLabel($filters['desa_id'] ?? null, $report),
        ];
    }

    private function formatPeriodLabel(?string $dateFrom, ?string $dateTo): string
    {
        $fromLabel = $dateFrom ? Carbon::parse($dateFrom)->locale('id')->translatedFormat('d F Y') : null;
        $toLabel = $dateTo ? Carbon::parse($dateTo)->locale('id')->translatedFormat('d F Y') : null;

        if ($fromLabel && $toLabel) {
            return "{$fromLabel} s/d {$toLabel}";
        }

        if ($fromLabel) {
            return "Sejak {$fromLabel}";
        }

        if ($toLabel) {
            return "Sampai {$toLabel}";
        }

        return 'Semua Periode';
    }

    private function formatDesaLabel($desaId, string $report): string
    {
        if (! $desaId) {
            return $report === 'setoran_kecamatan'
                ? 'Semua Desa (Laporan Kecamatan)'
                : 'Semua Desa';
        }

        $desaName = Desa::query()->whereKey($desaId)->value('name');

        return $desaName ? "Desa {$desaName}" : "Desa ID {$desaId}";
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

        $keuanganRows = Pelanggan::query()
            ->with([
                'tagihans' => function ($query) use ($filters) {
                    $query->with(['meterRecord.petugas', 'pembayarans.petugas'])
                        ->when($filters['date_from'] ?? null, function (Builder $builder, $dateFrom) {
                            $builder->where(function (Builder $inner) use ($dateFrom) {
                                $inner->whereDate('due_date', '>=', $dateFrom)
                                    ->orWhereDate('created_at', '>=', $dateFrom);
                            });
                        })
                        ->when($filters['date_to'] ?? null, function (Builder $builder, $dateTo) {
                            $builder->where(function (Builder $inner) use ($dateTo) {
                                $inner->whereDate('due_date', '<=', $dateTo)
                                    ->orWhereDate('created_at', '<=', $dateTo);
                            });
                        })
                        ->orderByDesc('period')
                        ->orderByDesc('id');
                },
            ])
            ->when($filters['desa_id'] ?? null, fn (Builder $query, $desaId) => $query->where('desa_id', $desaId))
            ->orderBy('name')
            ->get()
            ->map(function (Pelanggan $pelanggan) {
                $tagihans = $pelanggan->tagihans;
                $latestTagihan = $tagihans->first();
                $latestMeter = $latestTagihan?->meterRecord;
                $totalPemakaian = (float) $tagihans->sum('usage_m3');
                $totalPembayaran = (float) $tagihans->flatMap->pembayarans->sum('amount');
                $totalTagihan = (float) $tagihans->sum('amount');
                $totalTunggakan = max(0, $totalTagihan - $totalPembayaran);
                $lastPembayaran = $tagihans->flatMap->pembayarans->sortByDesc('paid_at')->first();
                $petugas = $lastPembayaran?->petugas?->name
                    ?? $latestMeter?->petugas?->name
                    ?? '-';

                return [
                    'kode_pelanggan' => $pelanggan->kode_pelanggan ?: '-',
                    'nama_pelanggan' => $pelanggan->name,
                    'pemakaian_bulan_lalu' => (float) ($latestMeter?->meter_previous_month ?? 0),
                    'pemakaian_bulan_ini' => (float) ($latestMeter?->meter_current_month ?? 0),
                    'total_pemakaian' => $totalPemakaian,
                    'total_pembayaran' => $totalPembayaran,
                    'tunggakan' => $totalTunggakan,
                    'status' => $totalTunggakan <= 0 ? 'Lunas' : 'Tidak Lunas',
                    'petugas' => $petugas,
                ];
            })
            ->filter(fn (array $row) => ! empty($row['nama_pelanggan']))
            ->values()
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
