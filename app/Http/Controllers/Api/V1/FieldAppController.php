<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PelangganResource;
use App\Models\LaporanGangguan;
use App\Models\MeterRecord;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class FieldAppController extends Controller
{
    public function pelangganIndex(Request $request)
    {
        $query = Pelanggan::query()->orderBy('name');
        if (! $request->user()->isKecamatanLevel()) {
            $query->where('desa_id', $request->user()->desa_id);
        }

        return PelangganResource::collection($query->paginate(20));
    }

    public function pelangganShow(Request $request, Pelanggan $pelanggan)
    {
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        return new PelangganResource($pelanggan);
    }

    public function meterIndex(Request $request)
    {
        $query = MeterRecord::with('pelanggan')->orderByDesc('recorded_at');
        if (! $request->user()->isKecamatanLevel()) {
            $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        return response()->json(['data' => $query->paginate(20)]);
    }

    public function meterStore(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => ['required', 'integer', 'exists:pelanggans,id'],
            'meter_previous_month' => ['required', 'integer', 'min:0'],
            'meter_current_month' => ['required', 'integer', 'min:0'],
            'recorded_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_recorded_at' => ['nullable', 'date'],
        ]);

        $pelanggan = Pelanggan::findOrFail($data['pelanggan_id']);
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        $data['petugas_id'] = $request->user()->id;
        $data['verification_status'] = 'pending';
        $data['is_anomaly'] = (int) $data['meter_current_month'] < (int) $data['meter_previous_month'];

        return response()->json(['message' => 'Meter record tersimpan.', 'data' => MeterRecord::create($data)], 201);
    }

    public function tagihanIndex(Request $request)
    {
        $query = Tagihan::with(['pelanggan', 'pembayarans'])->orderByDesc('period');
        if (! $request->user()->isKecamatanLevel()) {
            $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        return response()->json(['data' => $query->paginate(20)]);
    }

    public function pembayaranStore(Request $request)
    {
        $data = $request->validate([
            'tagihan_id' => ['required', 'integer', 'exists:tagihans,id'],
            'payment_method' => ['required', 'in:tunai,transfer_bank,e_wallet'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tagihan = Tagihan::with('pelanggan')->findOrFail($data['tagihan_id']);
        if (! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);
        }

        $data['petugas_id'] = $request->user()->id;

        return response()->json(['message' => 'Pembayaran tersimpan.', 'data' => Pembayaran::create($data)], 201);
    }

    public function keluhanIndex(Request $request)
    {
        $query = LaporanGangguan::with(['pelanggan', 'reporter'])->latest('reported_at');
        if (! $request->user()->isKecamatanLevel()) {
            $query->where('desa_id', $request->user()->desa_id);
        }

        return response()->json(['data' => $query->paginate(20)]);
    }

    public function keluhanStore(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggans,id'],
            'pelapor' => ['required_without:pelanggan_id', 'nullable', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'jenis_laporan' => ['required', 'in:gangguan,keluhan'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'status_penanganan' => ['nullable', 'in:baru,diproses,selesai'],
            'lokasi_text' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tanggal_kejadian' => ['nullable', 'date'],
        ]);

        $pelanggan = ! empty($data['pelanggan_id']) ? Pelanggan::find($data['pelanggan_id']) : null;
        if ($pelanggan && ! $request->user()->isKecamatanLevel()) {
            $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        }

        $data['reported_by'] = $request->user()->id;
        $data['reported_at'] = now();
        $data['status_penanganan'] = $data['status_penanganan'] ?? 'baru';
        $data['kode_keluhan'] = 'KLH-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['desa_id'] = $pelanggan?->desa_id ?? $request->user()->desa_id;
        $data['kecamatan_id'] = $pelanggan?->kecamatan_id ?? $request->user()->kecamatan_id;
        $data['pelapor'] = $pelanggan?->name ?? $data['pelapor'];
        $data['no_hp'] = $data['no_hp'] ?: ($pelanggan?->phone ?? null);

        return response()->json(['message' => 'Keluhan tersimpan.', 'data' => LaporanGangguan::create($data)], 201);
    }

    public function dashboardRingkas(Request $request)
    {
        $desaId = $request->user()->desa_id;

        return response()->json([
            'data' => [
                'total_pelanggan' => Pelanggan::when($desaId, fn ($q) => $q->where('desa_id', $desaId))->count(),
                'total_tagihan_aktif' => Tagihan::when($desaId, fn ($q) => $q->whereHas('pelanggan', fn ($sq) => $sq->where('desa_id', $desaId)))->whereIn('status', ['draft', 'terbit', 'menunggak'])->count(),
                'total_keluhan_aktif' => LaporanGangguan::when($desaId, fn ($q) => $q->where('desa_id', $desaId))->whereIn('status_penanganan', ['baru', 'diproses'])->count(),
            ],
        ]);
    }

    public function monitoringPeta(Request $request)
    {
        $gpsLat = $request->query('gps_latitude');
        $gpsLng = $request->query('gps_longitude');
        $desaId = $request->user()->isKecamatanLevel() ? null : $request->user()->desa_id;

        $pelanggans = Pelanggan::query()
            ->when($desaId, fn ($q) => $q->where('desa_id', $desaId))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'kode_pelanggan', 'latitude', 'longitude']);

        $keluhans = LaporanGangguan::query()
            ->when($desaId, fn ($q) => $q->where('desa_id', $desaId))
            ->whereIn('status_penanganan', ['baru', 'diproses'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'kode_keluhan', 'judul', 'status_penanganan', 'latitude', 'longitude']);

        return response()->json([
            'data' => [
                'user_current_location' => [
                    'latitude' => $gpsLat,
                    'longitude' => $gpsLng,
                ],
                'fallback_center' => [
                    'latitude' => $gpsLat ?: -7.6189,
                    'longitude' => $gpsLng ?: 110.9507,
                ],
                'pelanggans' => $pelanggans,
                'keluhans' => $keluhans,
            ],
        ]);
    }
}
