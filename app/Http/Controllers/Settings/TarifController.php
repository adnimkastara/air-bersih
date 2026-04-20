<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Tarif;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TarifController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $desaTarifs = Tarif::query()
            ->with('desa')
            ->where('scope_type', Tarif::SCOPE_DESA)
            ->when(! $user->isRoot(), fn ($query) => $query->where('village_id', $user->desa_id))
            ->orderByDesc('status')
            ->orderBy('village_id')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('settings.tarif.index', [
            'desaTarifs' => $desaTarifs,
            'desas' => $user->isRoot() ? Desa::orderBy('name')->get() : Desa::where('id', $user->desa_id)->get(),
            'canManageKecamatanTarif' => $user->isRoot(),
        ]);
    }

    public function kecamatan(Request $request): View
    {
        abort_unless($request->user()->isRoot(), 403);

        $kecamatanTarifs = Tarif::query()
            ->where('scope_type', Tarif::SCOPE_KECAMATAN)
            ->orderByDesc('status')
            ->orderBy('name')
            ->get();

        return view('settings.tarif.kecamatan', [
            'kecamatanTarifs' => $kecamatanTarifs,
        ]);
    }

    public function storeDesa(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'village_id' => $user->isRoot() ? ['required', 'exists:desas,id'] : ['nullable'],
            'category' => ['required', 'string', 'max:100'],
            'abonemen' => ['required', 'numeric', 'min:0'],
            'tarif_dasar' => ['required', 'numeric', 'min:0'],
            'tarif_per_m3' => ['required', 'numeric', 'min:0'],
            'late_fee_per_day' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        if (! $user->isRoot()) {
            $data['village_id'] = $user->desa_id;
        }

        $this->abortUnlessCanAccessDesa($request, $data['village_id']);

        Tarif::create([
            'scope_type' => Tarif::SCOPE_DESA,
            'village_id' => $data['village_id'],
            'name' => $data['name'],
            'category' => $data['category'],
            'customer_type' => $data['category'],
            'abonemen' => $data['abonemen'],
            'tarif_dasar' => $data['tarif_dasar'],
            'tarif_per_m3' => $data['tarif_per_m3'],
            'base_rate' => $data['tarif_dasar'],
            'usage_rate' => $data['tarif_per_m3'],
            'late_fee_per_day' => $data['late_fee_per_day'] ?? 0,
            'status' => $data['status'],
            'is_active' => $data['status'] === 'aktif',
            'effective_start' => now()->toDateString(),
        ]);

        return back()->with('status', 'Tarif desa berhasil disimpan.');
    }

    public function updateDesa(Request $request, Tarif $tarif): RedirectResponse
    {
        abort_unless($tarif->scope_type === Tarif::SCOPE_DESA, 404);

        $user = $request->user();
        $this->abortUnlessCanAccessDesa($request, $tarif->village_id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'village_id' => $user->isRoot() ? ['required', 'exists:desas,id'] : ['nullable'],
            'category' => ['required', 'string', 'max:100'],
            'abonemen' => ['required', 'numeric', 'min:0'],
            'tarif_dasar' => ['required', 'numeric', 'min:0'],
            'tarif_per_m3' => ['required', 'numeric', 'min:0'],
            'late_fee_per_day' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        if (! $user->isRoot()) {
            $data['village_id'] = $user->desa_id;
        }

        $this->abortUnlessCanAccessDesa($request, $data['village_id']);

        $tarif->update([
            'village_id' => $data['village_id'],
            'name' => $data['name'],
            'category' => $data['category'],
            'customer_type' => $data['category'],
            'abonemen' => $data['abonemen'],
            'tarif_dasar' => $data['tarif_dasar'],
            'tarif_per_m3' => $data['tarif_per_m3'],
            'base_rate' => $data['tarif_dasar'],
            'usage_rate' => $data['tarif_per_m3'],
            'late_fee_per_day' => $data['late_fee_per_day'] ?? 0,
            'status' => $data['status'],
            'is_active' => $data['status'] === 'aktif',
        ]);

        return back()->with('status', 'Tarif desa berhasil diperbarui.');
    }

    public function destroyDesa(Request $request, Tarif $tarif): RedirectResponse
    {
        abort_unless($tarif->scope_type === Tarif::SCOPE_DESA, 404);

        $this->abortUnlessCanAccessDesa($request, $tarif->village_id);
        $tarif->delete();

        return back()->with('status', 'Tarif desa berhasil dihapus.');
    }

    public function storeKecamatan(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isRoot(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tarif_per_m3' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        if ($data['status'] === 'aktif') {
            Tarif::where('scope_type', Tarif::SCOPE_KECAMATAN)->update(['status' => 'nonaktif', 'is_active' => false]);
        }

        Tarif::updateOrCreate(
            ['scope_type' => Tarif::SCOPE_KECAMATAN, 'category' => 'kecamatan'],
            [
                'name' => $data['name'],
                'customer_type' => 'kecamatan',
                'abonemen' => 0,
                'tarif_dasar' => 0,
                'tarif_per_m3' => $data['tarif_per_m3'],
                'base_rate' => 0,
                'usage_rate' => $data['tarif_per_m3'],
                'status' => $data['status'],
                'is_active' => $data['status'] === 'aktif',
                'effective_start' => now()->toDateString(),
                'effective_end' => null,
            ]
        );

        return back()->with('status', 'Tarif kecamatan berhasil disimpan. Semua desa otomatis memakai nominal yang sama.');
    }

    public function updateKecamatan(Request $request, Tarif $tarif): RedirectResponse
    {
        abort_unless($request->user()->isRoot(), 403);
        abort_unless($tarif->scope_type === Tarif::SCOPE_KECAMATAN, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tarif_per_m3' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        if ($data['status'] === 'aktif') {
            Tarif::where('scope_type', Tarif::SCOPE_KECAMATAN)
                ->where('id', '!=', $tarif->id)
                ->update(['status' => 'nonaktif', 'is_active' => false]);
        }

        $tarif->update([
            'name' => $data['name'],
            'category' => 'kecamatan',
            'customer_type' => 'kecamatan',
            'abonemen' => 0,
            'tarif_dasar' => 0,
            'tarif_per_m3' => $data['tarif_per_m3'],
            'base_rate' => 0,
            'usage_rate' => $data['tarif_per_m3'],
            'status' => $data['status'],
            'is_active' => $data['status'] === 'aktif',
            'effective_start' => now()->toDateString(),
        ]);

        return back()->with('status', 'Tarif kecamatan berhasil diperbarui.');
    }

    public function destroyKecamatan(Request $request, Tarif $tarif): RedirectResponse
    {
        abort_unless($request->user()->isRoot(), 403);
        abort_unless($tarif->scope_type === Tarif::SCOPE_KECAMATAN, 404);

        $tarif->delete();

        return back()->with('status', 'Tarif kecamatan berhasil dihapus.');
    }
}
