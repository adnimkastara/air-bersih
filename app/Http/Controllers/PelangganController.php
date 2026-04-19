<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index()
    {
        return view('pelanggan.index', [
            'pelanggans' => Pelanggan::with(['kecamatan', 'desa', 'assignedPetugas'])->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('pelanggan.create', [
            'kecamatans' => Kecamatan::orderBy('name')->get(),
            'desas' => Desa::orderBy('name')->get(),
            'petugas' => User::whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_id' => ['nullable', 'exists:desas,id'],
            'assigned_petugas_id' => ['nullable', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $pelanggan = Pelanggan::create($data);
        $this->logActivity($request, 'create_pelanggan', Pelanggan::class, $pelanggan->id, "Membuat pelanggan {$pelanggan->name}");

        return redirect()->route('pelanggan.index')->with('status', 'Pelanggan berhasil dibuat.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', [
            'pelanggan' => $pelanggan,
            'kecamatans' => Kecamatan::orderBy('name')->get(),
            'desas' => Desa::orderBy('name')->get(),
            'petugas' => User::whereHas('role', fn ($query) => $query->where('name', 'petugas_lapangan'))->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_id' => ['nullable', 'exists:desas,id'],
            'assigned_petugas_id' => ['nullable', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

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
}
