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

        $query = Pelanggan::with(['kecamatan', 'desa', 'assignedPetugas'])->orderBy('name');
        $query = $this->scopeByDesa($request, $query);

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
            $this->abortUnlessCanAccessDesa($request, $filters['desa_id']);
            $query->where('desa_id', $filters['desa_id']);
        }

        if (! empty($filters['assigned_petugas_id'])) {
            $query->where('assigned_petugas_id', $filters['assigned_petugas_id']);
        }

        $user = $request->user();
        $desaOptions = $user->isRoot()
            ? Desa::orderBy('name')->get()
            : Desa::where('id', $user->desa_id)->orderBy('name')->get();

        $pelanggans = $query->paginate(12)->withQueryString();

        return view('pelanggan.index', [
            'pelanggans' => $pelanggans,
            'desas' => $desaOptions,
            'petugas' => $this->petugasOptions($request),
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

    public function create(Request $request)
    {
        $user = $request->user();

        return view('pelanggan.create', [
            'kecamatans' => $user->isRoot() ? Kecamatan::orderBy('name')->get() : Kecamatan::whereHas('desas', fn ($q) => $q->where('id', $user->desa_id))->get(),
            'desas' => $user->isRoot() ? Desa::orderBy('name')->get() : Desa::where('id', $user->desa_id)->orderBy('name')->get(),
            'petugas' => $this->petugasOptions($request),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePelanggan($request);

        if (! $request->user()->isRoot()) {
            $data['desa_id'] = $request->user()->desa_id;
        }

        $this->abortUnlessCanAccessDesa($request, $data['desa_id']);

        $pelanggan = Pelanggan::create($data);
        $this->logActivity($request, 'create_pelanggan', Pelanggan::class, $pelanggan->id, "Membuat pelanggan {$pelanggan->name}");

        return redirect()->route('pelanggan.index')->with('status', 'Pelanggan berhasil dibuat.');
    }

    public function show(Request $request, Pelanggan $pelanggan)
    {
        $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        $pelanggan->load(['kecamatan', 'desa', 'assignedPetugas']);

        return view('pelanggan.show', ['pelanggan' => $pelanggan]);
    }

    public function edit(Request $request, Pelanggan $pelanggan)
    {
        $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);

        $user = $request->user();

        return view('pelanggan.edit', [
            'pelanggan' => $pelanggan,
            'kecamatans' => $user->isRoot() ? Kecamatan::orderBy('name')->get() : Kecamatan::whereHas('desas', fn ($q) => $q->where('id', $user->desa_id))->get(),
            'desas' => $user->isRoot() ? Desa::orderBy('name')->get() : Desa::where('id', $user->desa_id)->get(),
            'petugas' => $this->petugasOptions($request),
        ]);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
        $data = $this->validatePelanggan($request, $pelanggan->id);

        if (! $request->user()->isRoot()) {
            $data['desa_id'] = $request->user()->desa_id;
        }

        $this->abortUnlessCanAccessDesa($request, $data['desa_id']);

        $pelanggan->update($data);
        $this->logActivity($request, 'update_pelanggan', Pelanggan::class, $pelanggan->id, "Memperbarui pelanggan {$pelanggan->name}");

        return redirect()->route('pelanggan.index')->with('status', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Request $request, Pelanggan $pelanggan)
    {
        $this->abortUnlessCanAccessDesa($request, $pelanggan->desa_id);
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

    protected function petugasOptions(Request $request)
    {
        return User::whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))
            ->when(! $request->user()->isRoot(), fn ($query) => $query->where('desa_id', $request->user()->desa_id))
            ->orderBy('name')
            ->get();
    }
}
