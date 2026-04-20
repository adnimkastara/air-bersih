<?php

namespace App\Http\Controllers;

use App\Models\MeterRecord;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeterRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = MeterRecord::with(['pelanggan', 'petugas'])
            ->orderByDesc('recorded_at')
            ->orderByDesc('id');

        if (! $request->user()->isRoot()) {
            $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        return view('meter_records.index', [
            'meterRecords' => $query->paginate(15),
        ]);
    }

    public function create(Request $request)
    {
        $pelangganQuery = Pelanggan::orderBy('name');
        $petugasQuery = User::whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))->orderBy('name');

        if (! $request->user()->isRoot()) {
            $pelangganQuery->where('desa_id', $request->user()->desa_id);
            $petugasQuery->where('desa_id', $request->user()->desa_id);
        }

        $pelanggans = $pelangganQuery->get();
        $lastRecords = MeterRecord::query()
            ->whereIn('pelanggan_id', $pelanggans->pluck('id'))
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get()
            ->unique('pelanggan_id')
            ->mapWithKeys(fn (MeterRecord $record) => [$record->pelanggan_id => (int) $record->meter_current_month]);

        $selectedPelangganId = old('pelanggan_id', $request->query('pelanggan_id'));
        $defaultPreviousMeter = old('meter_previous_month');
        if ($defaultPreviousMeter === null && $selectedPelangganId) {
            $defaultPreviousMeter = $lastRecords->get((int) $selectedPelangganId, 0);
        }

        return view('meter_records.create', [
            'pelanggans' => $pelanggans,
            'petugas' => $petugasQuery->get(),
            'lastMeters' => $lastRecords,
            'defaultPreviousMeter' => $defaultPreviousMeter,
            'selectedPelangganId' => $selectedPelangganId,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id' => ['required', 'exists:pelanggans,id'],
            'petugas_id' => ['nullable', 'exists:users,id'],
            'meter_previous_month' => ['required', 'integer', 'min:0'],
            'meter_current_month' => ['required', 'integer', 'min:0'],
            'recorded_at' => ['required', 'date'],
            'meter_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'verification_status' => ['required', 'in:pending,terverifikasi,ditolak'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $previous = (int) $request->input('meter_previous_month', 0);
            $current = (int) $request->input('meter_current_month', 0);

            if ($current < 0 || $previous < 0) {
                $validator->errors()->add('meter_current_month', 'Nilai meter harus bernilai positif.');
            }

            if ($current < $previous && blank($request->input('notes'))) {
                $validator->errors()->add('notes', 'Jika meter bulan ini lebih kecil dari bulan lalu, mohon isi catatan (contoh: ganti meter/koreksi).');
            }
        });

        $data = $validator->validate();

        $pelanggan = Pelanggan::findOrFail($data['pelanggan_id']);
        $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);

        if ($request->user()->hasRole('petugas_lapangan')) {
            $data['petugas_id'] = $request->user()->id;
        }

        $lastRecord = MeterRecord::where('pelanggan_id', $data['pelanggan_id'])
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();

        $referencePrevious = $lastRecord?->meter_current_month ?? $data['meter_previous_month'];

        $isAnomaly = (int) $data['meter_current_month'] < (int) $referencePrevious;

        if ($isAnomaly && empty($data['notes'])) {
            $data['notes'] = 'Anomali: meter bulan ini lebih kecil dari bulan lalu. Mohon verifikasi ulang lapangan.';
        }

        if ($request->hasFile('meter_photo')) {
            $data['meter_photo_path'] = $request->file('meter_photo')->store('meter-photos', 'public');
        }

        unset($data['meter_photo']);
        $data['is_anomaly'] = $isAnomaly;

        $record = MeterRecord::create($data);
        $this->logActivity(
            $request,
            $isAnomaly ? 'create_meter_record_anomaly' : 'create_meter_record',
            MeterRecord::class,
            $record->id,
            "Mencatat meter pelanggan {$record->pelanggan_id}"
        );

        $message = $isAnomaly
            ? 'Pencatatan meter disimpan dengan status anomali. Mohon lakukan verifikasi.'
            : 'Pencatatan meter berhasil disimpan.';

        return redirect()->route('meter_records.index')->with('status', $message);
    }
}
