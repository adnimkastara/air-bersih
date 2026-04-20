<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DesaController extends Controller
{
    public function index()
    {
        return view('desa.index', [
            'desas' => Desa::with('kecamatan')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('desa.create', [
            'kecamatans' => Kecamatan::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'kode_desa' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\\-]+$/',
                Rule::unique('desas', 'kode_desa')->where(fn ($query) => $query->where('kecamatan_id', $request->input('kecamatan_id'))),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $desa = Desa::create($data);
        $this->logActivity($request, 'create_desa', Desa::class, $desa->id, "Membuat desa {$desa->name}");

        return redirect()->route('desa.index')->with('status', 'Desa berhasil dibuat.');
    }

    public function edit(Desa $desa)
    {
        return view('desa.edit', [
            'desa' => $desa,
            'kecamatans' => Kecamatan::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Desa $desa)
    {
        $data = $request->validate([
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'kode_desa' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\\-]+$/',
                Rule::unique('desas', 'kode_desa')
                    ->ignore($desa->id)
                    ->where(fn ($query) => $query->where('kecamatan_id', $request->input('kecamatan_id'))),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $desa->update($data);
        $this->logActivity($request, 'update_desa', Desa::class, $desa->id, "Memperbarui desa {$desa->name}");

        return redirect()->route('desa.index')->with('status', 'Desa berhasil diperbarui.');
    }

    public function destroy(Request $request, Desa $desa)
    {
        $name = $desa->name;
        $desa->delete();
        $this->logActivity($request, 'delete_desa', Desa::class, $desa->id, "Menghapus desa $name");

        return redirect()->route('desa.index')->with('status', 'Desa telah dihapus.');
    }
}
