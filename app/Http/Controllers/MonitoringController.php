<?php

namespace App\Http\Controllers;

use App\Models\LaporanGangguan;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:aktif,menunggak,gangguan'],
            'jenis_laporan' => ['nullable', 'in:gangguan,keluhan'],
            'status_penanganan' => ['nullable', 'in:baru,diproses,selesai'],
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggans,id'],
        ]);

        $pelangganQuery = Pelanggan::with(['desa', 'kecamatan']);
        $pelanggans = $pelangganQuery
            ->withCount([
                'tagihans as menunggak_count' => fn ($query) => $query->where('status', 'menunggak'),
                'laporanGangguans as gangguan_aktif_count' => fn ($query) => $query->where('jenis_laporan', 'gangguan')->whereIn('status_penanganan', ['baru', 'diproses']),
            ])
            ->orderBy('name')
            ->when(! $request->user()->isKecamatanLevel(), fn ($query) => $query->where('desa_id', $request->user()->desa_id))
            ->get()
            ->map(function ($pelanggan) {
                $pelanggan->monitoring_status = $this->resolveStatus($pelanggan);

                return $pelanggan;
            });


        if (! empty($filters['status'])) {
            $pelanggans = $pelanggans->filter(fn ($pelanggan) => $pelanggan->monitoring_status === $filters['status'])->values();
        }

        $laporanQuery = LaporanGangguan::with(['pelanggan', 'reporter'])
            ->latest('reported_at')
            ->latest('created_at');

        if (! $request->user()->isKecamatanLevel()) {
            $laporanQuery->whereHas('pelanggan', fn ($query) => $query->where('desa_id', $request->user()->desa_id));
        }

        if (! empty($filters['jenis_laporan'])) {
            $laporanQuery->where('jenis_laporan', $filters['jenis_laporan']);
        }

        if (! empty($filters['status_penanganan'])) {
            $laporanQuery->where('status_penanganan', $filters['status_penanganan']);
        }

        if (! empty($filters['pelanggan_id'])) {
            $laporanQuery->where('pelanggan_id', $filters['pelanggan_id']);
        }

        return view('monitoring.index', [
            'filters' => $filters,
            'pelanggans' => $pelanggans,
            'pelangganOptions' => Pelanggan::when(! $request->user()->isKecamatanLevel(), fn ($query) => $query->where('desa_id', $request->user()->desa_id))->orderBy('name')->get(['id', 'name', 'kode_pelanggan']),
            'laporans' => $laporanQuery->paginate(10)->withQueryString(),
            'mapPoints' => $pelanggans
                ->filter(fn ($pelanggan) => $pelanggan->latitude && $pelanggan->longitude)
                ->map(fn ($pelanggan) => [
                    'name' => $pelanggan->name,
                    'kode' => $pelanggan->kode_pelanggan,
                    'lat' => (float) $pelanggan->latitude,
                    'lng' => (float) $pelanggan->longitude,
                    'status' => $pelanggan->monitoring_status,
                    'status_label' => ucfirst($pelanggan->monitoring_status),
                    'show_url' => route('pelanggan.show', $pelanggan),
                ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => ['required', 'integer', 'exists:pelanggans,id'],
            'pelapor' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'jenis_laporan' => ['required', 'in:gangguan,keluhan'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'prioritas' => ['nullable', 'in:rendah,sedang,tinggi'],
            'status_penanganan' => ['required', 'in:baru,diproses,selesai'],
            'lokasi_text' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_gangguan' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $pelanggan = Pelanggan::findOrFail($data['pelanggan_id']);
        $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);

        $fotoPath = $request->hasFile('foto_gangguan')
            ? $request->file('foto_gangguan')->store('gangguan', 'public')
            : null;

        $payload = [
            'pelanggan_id' => $data['pelanggan_id'],
            'kode_keluhan' => 'KLH-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'desa_id' => $pelanggan->desa_id,
            'kecamatan_id' => $pelanggan->kecamatan_id,
            'reported_by' => $request->user()?->id,
            'pelapor' => $pelanggan->name,
            'no_hp' => $data['no_hp'] ?: $pelanggan->phone,
            'jenis_laporan' => $data['jenis_laporan'],
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'],
            'lokasi_text' => $data['lokasi_text'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status_penanganan' => $data['status_penanganan'],
            'foto_path' => $fotoPath,
            'reported_at' => Carbon::now(),
        ];
        if (LaporanGangguan::hasPrioritasColumn()) {
            $payload['prioritas'] = $data['prioritas'] ?? 'sedang';
        }

        $payload = LaporanGangguan::filterExistingColumns($payload);
        $laporan = LaporanGangguan::create($payload);

        $this->logActivity(
            $request,
            'create_laporan_gangguan',
            LaporanGangguan::class,
            $laporan->id,
            "Membuat laporan {$laporan->jenis_laporan} untuk pelanggan #{$laporan->pelanggan_id}"
        );

        return redirect()->route('monitoring.index')->with('status', 'Laporan gangguan/keluhan berhasil disimpan.');
    }

    protected function resolveStatus(Pelanggan $pelanggan): string
    {
        if ($pelanggan->gangguan_aktif_count > 0) {
            return 'gangguan';
        }

        if ($pelanggan->menunggak_count > 0) {
            return 'menunggak';
        }

        return 'aktif';
    }
}
