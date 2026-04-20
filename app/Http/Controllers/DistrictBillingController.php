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
        abort_unless($request->user()->isRoot(), 403);
        $data = $this->loadBillingsByPeriod($request);

        return view('district_billings.index', $data);
    }

    public function payments(Request $request): View
    {
        abort_unless($request->user()->isRoot(), 403);
        $data = $this->loadBillingsByPeriod($request);

        return view('district_billings.payments', $data);
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
                    'paid_amount' => 0,
                    'status' => 'terbit',
                    'payment_status' => 'belum_bayar',
                    'paid_at' => null,
                    'payment_method' => null,
                    'payment_notes' => null,
                    'due_date' => Carbon::createFromFormat('Y-m-d', $period.'-01')->endOfMonth()->addDays(10)->toDateString(),
                    'generated_at' => now(),
                ]
            );
        }

        return redirect()->route('district-billings.index', ['period' => $period])->with('status', 'Tagihan desa ke kecamatan berhasil digenerate.');
    }

    public function recordPayment(Request $request, DistrictBilling $districtBilling): RedirectResponse
    {
        abort_unless($request->user()->isRoot(), 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'paid_at' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'payment_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newPaidAmount = (float) $districtBilling->paid_amount + (float) $data['amount'];
        $isLunas = $newPaidAmount >= (float) $districtBilling->total_setoran;

        $districtBilling->update([
            'paid_amount' => $newPaidAmount,
            'paid_at' => $data['paid_at'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_notes' => $data['payment_notes'] ?? null,
            'payment_status' => $isLunas ? 'lunas' : 'sebagian',
            'status' => $isLunas ? 'lunas' : 'terbit',
        ]);

        return back()->with('status', 'Pembayaran setoran desa berhasil dicatat.');
    }

    private function loadBillingsByPeriod(Request $request): array
    {
        $user = $request->user();
        $period = $request->input('period', now()->format('Y-m'));

        $query = DistrictBilling::query()->with('desa')->where('period', $period)->orderBy('desa_id');

        if (! $user->isRoot()) {
            $query->where('desa_id', $user->desa_id);
        }

        $billings = $query->get();

        return [
            'billings' => $billings,
            'period' => $period,
            'canGenerate' => $user->isRoot(),
            'canRecordPayment' => $user->isRoot() || $user->isAdminDesa(),
            'totalTagihanDesa' => (float) $billings->sum('total_setoran'),
            'totalPembayaranDesa' => (float) $billings->sum('paid_amount'),
            'desaLunas' => $billings->where('payment_status', 'lunas')->count(),
            'desaMenunggak' => $billings->whereIn('payment_status', ['belum_bayar', 'sebagian'])->count(),
        ];
    }
}
