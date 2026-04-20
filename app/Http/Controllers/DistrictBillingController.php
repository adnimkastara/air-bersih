<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\DistrictBilling;
use App\Models\MeterRecord;
use App\Models\Tarif;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistrictBillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $period = $request->input('period', now()->format('Y-m'));

        $query = DistrictBilling::query()->with('desa')->where('period', $period)->orderBy('desa_id');

        if (! $user->isRoot()) {
            $query->where('desa_id', $user->desa_id);
        }

        return view('district_billings.index', [
            'billings' => $query->get(),
            'period' => $period,
            'canGenerate' => $user->isRoot(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isRoot(), 403);

        $period = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ])['period'];

        $tarifKecamatan = Tarif::query()
            ->where('scope_type', Tarif::SCOPE_KECAMATAN)
            ->where('status', 'aktif')
            ->orderByDesc('effective_start')
            ->first();

        if (! $tarifKecamatan) {
            return back()->with('status', 'Belum ada tarif kecamatan aktif.');
        }

        $start = Carbon::createFromFormat('Y-m-d', $period.'-01')->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m-d', $period.'-01')->endOfMonth()->toDateString();

        $desas = Desa::query()->orderBy('name')->get();

        foreach ($desas as $desa) {
            $usage = MeterRecord::query()
                ->whereBetween('recorded_at', [$start, $end])
                ->whereHas('pelanggan', fn ($query) => $query->where('desa_id', $desa->id))
                ->get()
                ->sum(fn (MeterRecord $record) => max(0, (int) $record->meter_current_month - (int) $record->meter_previous_month));

            DistrictBilling::updateOrCreate(
                ['desa_id' => $desa->id, 'period' => $period],
                [
                    'tarif_id' => $tarifKecamatan->id,
                    'total_usage_m3' => $usage,
                    'tarif_per_m3' => $tarifKecamatan->tarif_per_m3,
                    'total_setoran' => $usage * (float) $tarifKecamatan->tarif_per_m3,
                    'status' => 'terbit',
                    'due_date' => Carbon::createFromFormat('Y-m-d', $period.'-01')->endOfMonth()->addDays(10)->toDateString(),
                    'generated_at' => now(),
                ]
            );
        }

        return redirect()->route('district-billings.index', ['period' => $period])->with('status', 'Rekap setoran desa ke kecamatan berhasil digenerate.');
    }
}
