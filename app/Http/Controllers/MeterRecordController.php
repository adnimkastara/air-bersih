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
            'meterRecords' => MeterRecord::with(['pelanggan', 'petugas'])->orderByDesc('recorded_at')->get(),
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
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['meter_current_month'] < $data['meter_previous_month']) {
            return back()->withErrors(['meter_current_month' => 'Meter bulan ini tidak boleh kurang dari meter bulan lalu.'])->withInput();
        }

        $record = MeterRecord::create($data);
        $this->logActivity($request, 'create_meter_record', MeterRecord::class, $record->id, "Mencatat meter pelanggan {$record->pelanggan_id}");

        return redirect()->route('meter_records.index')->with('status', 'Pencatatan meter berhasil disimpan.');
    }
}
