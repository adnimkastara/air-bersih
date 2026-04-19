<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
            'desa_id' => ['nullable', 'integer', 'exists:desas,id'],
            'assigned_petugas_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $query = Pelanggan::with(['kecamatan', 'desa', 'assignedPetugas'])
            ->orderBy('name');

        if (! empty($filters['q'])) {
            $keyword = $filters['q'];
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('kode_pelanggan', 'like', "%{$keyword}%")
                    ->orWhere('nomor_meter', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('dusun', 'like', "%{$keyword}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['desa_id'])) {
            $query->where('desa_id', $filters['desa_id']);
        }

        if (! empty($filters['assigned_petugas_id'])) {
            $query->where('assigned_petugas_id', $filters['assigned_petugas_id']);
        }

        $pelanggans = $query->paginate(12)->withQueryString();

        return view('pelanggan.index', [
            'pelanggans' => $pelanggans,
            'desas' => Desa::orderBy('name')->get(),
            'petugas' => $this->petugasOptions(),
            'filters' => $filters,
            'mapPoints' => $pelanggans->getCollection()
                ->filter(fn ($pelanggan) => $pelanggan->latitude && $pelanggan->longitude)
                ->map(fn ($pelanggan) => [
                    'name' => $pelanggan->name,
                    'kode' => $pelanggan->kode_pelanggan,
                    'lat' => (float) $pelanggan->latitude,
                    'lng' => (float) $pelanggan->longitude,
                    'show_url' => route('pelanggan.show', $pelanggan),
                ])->values(),
        ]);
    }

    public function create()
    {
        return view('pelanggan.create', [
            'kecamatans' => Kecamatan::orderBy('name')->get(),
            'desas' => Desa::orderBy('name')->get(),
            'petugas' => $this->petugasOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePelanggan($request);

        $pelanggan = Pelanggan::create($data);
        $this->logActivity($request, 'create_pelanggan', Pelanggan::class, $pelanggan->id, "Membuat pelanggan {$pelanggan->name}");

        return redirect()->route('pelanggan.index')->with('status', 'Pelanggan berhasil dibuat.');
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load(['kecamatan', 'desa', 'assignedPetugas']);

        return view('pelanggan.show', [
            'pelanggan' => $pelanggan,
        ]);
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', [
            'pelanggan' => $pelanggan,
            'kecamatans' => Kecamatan::orderBy('name')->get(),
            'desas' => Desa::orderBy('name')->get(),
            'petugas' => $this->petugasOptions(),
        ]);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $this->validatePelanggan($request, $pelanggan->id);

        $pelanggan->update($data);
        $this->logActivity($request, 'update_pelanggan', Pelanggan::class, $pelanggan->id, "Memperbarui pelanggan {$pelanggan->name}");

        return redirect()->route('pelanggan.index')->with('status', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Request $request, Pelanggan $pelanggan)
    {
        $name = $pelanggan->name;
        $pelanggan->delete();
        $this->logActivity($request, 'delete_pelanggan', Pelanggan::class, $pelanggan->id, "Menghapus pelanggan $name");

        return redirect()->route('pelanggan.index')->with('status', 'Pelanggan telah dihapus.');
    }

    protected function validatePelanggan(Request $request, ?int $pelangganId = null): array
    {
        return $request->validate([
            'kode_pelanggan' => ['required', 'string', 'max:50', Rule::unique('pelanggans', 'kode_pelanggan')->ignore($pelangganId)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'dusun' => ['required', 'string', 'max:255'],
            'jenis_pelanggan' => ['required', 'string', 'max:100'],
            'nomor_meter' => ['required', 'string', 'max:50', Rule::unique('pelanggans', 'nomor_meter')->ignore($pelangganId)],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_id' => ['required', 'exists:desas,id'],
            'assigned_petugas_id' => ['nullable', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);
    }

    protected function petugasOptions()
    {
        return User::whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))
            ->orderBy('name')
            ->get();
    }
}
