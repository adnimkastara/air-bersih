<?php

namespace App\Http\Controllers;

use App\Models\MeterRecord;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;

class MeterRecordController extends Controller
{
    public function index()
    {
        return view('meter_records.index', [
            'meterRecords' => MeterRecord::with(['pelanggan', 'petugas'])
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('meter_records.create', [
            'pelanggans' => Pelanggan::orderBy('name')->get(),
            'petugas' => User::whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => ['required', 'exists:pelanggans,id'],
            'petugas_id' => ['nullable', 'exists:users,id'],
            'meter_previous_month' => ['required', 'integer', 'min:0'],
            'meter_current_month' => ['required', 'integer', 'min:0'],
            'recorded_at' => ['required', 'date'],
            'meter_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'verification_status' => ['required', 'in:pending,terverifikasi,ditolak'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

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
