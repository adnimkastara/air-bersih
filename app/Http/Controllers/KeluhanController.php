<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\LaporanGangguan;
use App\Models\Pelanggan;
use App\Models\User;
use App\Notifications\KeluhanBaruNotification;
use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KeluhanController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status_penanganan' => ['nullable', 'in:baru,diproses,selesai'],
            'prioritas' => ['nullable', 'in:rendah,sedang,tinggi'],
            'jenis_laporan' => ['nullable', 'in:gangguan,keluhan'],
        ]);

        $query = LaporanGangguan::with(['pelanggan', 'reporter', 'desa', 'kecamatan'])
            ->latest('reported_at')
            ->latest('id');

        $query = $this->applyRoleScope($request, $query);

        $filterableColumns = ['status_penanganan', 'jenis_laporan'];
        if (LaporanGangguan::hasPrioritasColumn()) {
            $filterableColumns[] = 'prioritas';
        }

        foreach ($filterableColumns as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $mapPoints = collect();
        if (LaporanGangguan::hasCoordinateColumns()) {
            $mapPoints = (clone $query)->whereNotNull('latitude')->whereNotNull('longitude')->limit(200)->get()->map(fn ($item) => [
                'id' => $item->id,
                'judul' => $item->judul,
                'status' => $item->status_penanganan,
                'lat' => (float) $item->latitude,
                'lng' => (float) $item->longitude,
                'url' => route('keluhan.show', $item),
            ])->values();
        }

        return view('keluhan.index', [
            'laporans' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
            'mapPoints' => $mapPoints,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        return view('keluhan.create', [
            'pelanggans' => Pelanggan::query()
                ->when(! $user->isKecamatanLevel(), fn ($q) => $q->where('desa_id', $user->desa_id))
                ->orderBy('name')
                ->get(['id', 'name', 'kode_pelanggan', 'phone', 'desa_id', 'latitude', 'longitude']),
            'desas' => Desa::query()
                ->when(! $user->isKecamatanLevel(), fn ($q) => $q->where('id', $user->desa_id))
                ->orderBy('name')
                ->get(['id', 'name', 'kecamatan_id', 'latitude', 'longitude']),
            'defaultCenter' => [
                'lat' => -7.6189,
                'lng' => 110.9507,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateKeluhan($request);

        $pelanggan = ! empty($data['pelanggan_id']) ? Pelanggan::find($data['pelanggan_id']) : null;

        if ($pelanggan && ! $request->user()->canAccessVillage($pelanggan->desa_id)) {
            abort(403);
        }

        $data['reported_by'] = $request->user()->id;
        $data['reported_at'] = now();
        $data['kode_keluhan'] = 'KLH-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['desa_id'] = $data['desa_id'] ?? $pelanggan?->desa_id ?? $request->user()->desa_id;
        $data['kecamatan_id'] = $data['kecamatan_id'] ?? $pelanggan?->kecamatan_id ?? $request->user()->kecamatan_id;
        $data['pelapor'] = $this->resolvePelaporName($data['pelapor'] ?? null, $pelanggan?->name);
        $data['no_hp'] = $data['no_hp'] ?: ($pelanggan?->phone ?? null);
        if (! LaporanGangguan::hasCoordinateColumns()) {
            unset($data['latitude'], $data['longitude']);
        }
        if (! LaporanGangguan::hasPrioritasColumn()) {
            unset($data['prioritas']);
        }

        if ($request->hasFile('foto_gangguan')) {
            $data['foto_path'] = $request->file('foto_gangguan')->store('keluhan', 'public');
        }

        unset($data['foto_gangguan']);
        $laporan = LaporanGangguan::create($data);
        $this->sendPetugasNotifications($laporan);

        $this->logActivity($request, 'create_keluhan', LaporanGangguan::class, $laporan->id, 'Input keluhan/gangguan');

        return redirect()->route('keluhan.index')->with('status', 'Keluhan berhasil disimpan.');
    }

    public function show(Request $request, LaporanGangguan $laporanGangguan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $laporanGangguan->desa_id ?? $laporanGangguan->pelanggan?->desa_id);
        }

        return view('keluhan.show', ['laporan' => $laporanGangguan->load(['pelanggan', 'reporter', 'handler'])]);
    }

    public function update(Request $request, LaporanGangguan $laporanGangguan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $laporanGangguan->desa_id ?? $laporanGangguan->pelanggan?->desa_id);
        }

        $rules = [
            'status_penanganan' => ['required', 'in:baru,diproses,selesai'],
        ];
        if (LaporanGangguan::hasPrioritasColumn()) {
            $rules['prioritas'] = ['required', 'in:rendah,sedang,tinggi'];
        }
        $data = $request->validate($rules);

        $laporanGangguan->fill($data);
        if ($data['status_penanganan'] === 'selesai') {
            $laporanGangguan->tanggal_selesai = now();
            $laporanGangguan->ditangani_oleh = $request->user()->id;
        }
        $laporanGangguan->save();

        return back()->with('status', 'Status keluhan diperbarui.');
    }

    private function validateKeluhan(Request $request): array
    {
        return $request->validate([
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggans,id'],
            'pelapor' => ['required_without:pelanggan_id', 'nullable', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'desa_id' => ['nullable', 'integer', 'exists:desas,id'],
            'kecamatan_id' => ['nullable', 'integer', 'exists:kecamatans,id'],
            'jenis_laporan' => ['required', 'in:gangguan,keluhan'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'status_penanganan' => ['required', 'in:baru,diproses,selesai'],
            'lokasi_text' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tanggal_kejadian' => ['nullable', 'date'],
            'foto_gangguan' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);
    }

    private function sendPetugasNotifications(LaporanGangguan $laporan): void
    {
        $petugasQuery = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))
            ->where('is_active', true);

        if ($laporan->desa_id) {
            $petugasQuery->where('desa_id', $laporan->desa_id);
        } elseif ($laporan->kecamatan_id) {
            $petugasQuery->where('kecamatan_id', $laporan->kecamatan_id);
        }

        $petugasList = $petugasQuery->get();
        if ($petugasList->isEmpty()) {
            $petugasList = User::query()
                ->whereHas('role', fn (Builder $query) => $query->where('name', 'petugas_lapangan'))
                ->where('is_active', true)
                ->get();
        }

        $whatsAppService = app(WhatsAppService::class);

        foreach ($petugasList as $petugas) {
            $petugas->notify(new KeluhanBaruNotification($laporan));

            if (! empty($petugas->no_hp)) {
                $whatsAppService->sendMessage(
                    $petugas->no_hp,
                    $this->buildWhatsAppMessage($laporan)
                );
            }
        }
    }

    private function resolvePelaporName(?string $inputPelapor, ?string $pelangganName): ?string
    {
        $candidate = trim((string) $inputPelapor);
        if ($candidate !== '') {
            return $candidate;
        }

        return $pelangganName;
    }

    private function buildWhatsAppMessage(LaporanGangguan $laporan): string
    {
        $lokasi = ($laporan->latitude !== null && $laporan->longitude !== null)
            ? sprintf('https://maps.google.com/?q=%s,%s', $laporan->latitude, $laporan->longitude)
            : '-';

        return "Keluhan Baru:\n"
            ."Judul: {$laporan->judul}\n"
            .'Pelapor: '.($laporan->pelapor ?? '-')."\n"
            .'Prioritas: '.ucfirst($laporan->prioritas ?? 'sedang')."\n"
            ."Lokasi: {$lokasi}";
    }

    private function applyRoleScope(Request $request, $query)
    {
        $user = $request->user();

        if ($user->isKecamatanLevel()) {
            return $query->when($user->kecamatan_id, fn ($q) => $q->where('kecamatan_id', $user->kecamatan_id));
        }

        return $query->where(function ($q) use ($user) {
            $q->where('desa_id', $user->desa_id)
                ->orWhereHas('pelanggan', fn ($sub) => $sub->where('desa_id', $user->desa_id));
        });
    }
}
