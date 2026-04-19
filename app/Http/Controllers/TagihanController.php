<?php

namespace App\Http\Controllers;

use App\Models\MeterRecord;
use App\Models\Tagihan;
use App\Models\Tarif;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $this->refreshLateFees($request);

        $tarifQuery = Tarif::orderByDesc('is_active')->orderBy('customer_type')->orderBy('name');
        $tagihanQuery = Tagihan::with(['pelanggan', 'meterRecord', 'tarif'])
            ->orderByDesc('period')
            ->orderByDesc('created_at');

        if (! $request->user()->isRoot()) {
            $tagihanQuery->whereHas('pelanggan', fn ($query) => $query->where('desa_id', $request->user()->desa_id));
        }

        return view('tagihan.index', [
            'tarifs' => $tarifQuery->get(),
            'tagihans' => $tagihanQuery->get(),
            'selectedPeriod' => $request->input('period', now()->format('Y-m')),
        ]);
    }

    public function show(Request $request, Tagihan $tagihan)
    {
        $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);
        $tagihan->load(['pelanggan', 'meterRecord', 'tarif', 'pembayarans.petugas']);

        return view('tagihan.show', [
            'tagihan' => $tagihan,
            'totalPaid' => $tagihan->pembayarans->sum('amount'),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $period = Carbon::createFromFormat('Y-m', $validated['period'])->startOfMonth();
        $monthStart = $period->copy()->startOfMonth();
        $monthEnd = $period->copy()->endOfMonth();

        $meterRecords = MeterRecord::with('pelanggan')
            ->whereBetween('recorded_at', [$monthStart, $monthEnd])
            ->when(! $request->user()->isRoot(), fn ($query) => $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id)))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($meterRecords as $record) {
            if (! $record->pelanggan) {
                $skipped++;
                continue;
            }

            $exists = Tagihan::where('pelanggan_id', $record->pelanggan_id)
                ->where('period', $period->format('Y-m'))
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $tarif = $record->pelanggan->activeTarif();
            if (! $tarif) {
                $skipped++;
                continue;
            }

            $usage = max(0, (int) ($record->meter_current_month - $record->meter_previous_month));
            $baseAmount = (float) $tarif->base_rate;
            $usageAmount = $usage * (float) $tarif->usage_rate;
            $lateFee = 0;
            $totalAmount = $baseAmount + $usageAmount + $lateFee;

            Tagihan::create([
                'pelanggan_id' => $record->pelanggan_id,
                'meter_record_id' => $record->id,
                'tarif_id' => $tarif->id,
                'amount' => $totalAmount,
                'status' => 'draft',
                'due_date' => $monthEnd->copy()->addDays(10),
                'period' => $period->format('Y-m'),
                'usage_m3' => $usage,
                'base_amount' => $baseAmount,
                'usage_amount' => $usageAmount,
                'late_fee' => $lateFee,
                'generated_at' => now(),
            ]);

            $created++;
        }

        $this->logActivity(
            $request,
            'generate_tagihan',
            Tagihan::class,
            null,
            "Generate tagihan {$period->format('Y-m')}: {$created} dibuat, {$skipped} dilewati."
        );

        return redirect()->route('tagihan.index')->with('status', "Periode {$period->format('Y-m')}: {$created} tagihan dibuat, {$skipped} dilewati.");
    }

    public function publish(Request $request, Tagihan $tagihan)
    {
        $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);
        $tagihan->status = 'terbit';
        $tagihan->save();
        $this->logActivity($request, 'publish_tagihan', Tagihan::class, $tagihan->id, "Menerbitkan tagihan {$tagihan->id}");

        return back()->with('status', 'Tagihan berhasil diterbitkan.');
    }

    private function refreshLateFees(Request $request): void
    {
        $openBills = Tagihan::with(['tarif', 'pelanggan'])
            ->whereIn('status', ['draft', 'terbit', 'menunggak'])
            ->when(! $request->user()->isRoot(), fn ($query) => $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id)))
            ->get();

        foreach ($openBills as $bill) {
            $daysLate = $bill->due_date && now()->greaterThan($bill->due_date)
                ? $bill->due_date->diffInDays(now())
                : 0;

            $lateFeePerDay = (float) ($bill->tarif?->late_fee_per_day ?? 0);
            $lateFee = $daysLate * $lateFeePerDay;
            $recalculatedAmount = (float) $bill->base_amount + (float) $bill->usage_amount + $lateFee;

            $paid = $bill->pembayarans()->sum('amount');

            if ($paid >= $recalculatedAmount) {
                $status = 'lunas';
            } elseif ($daysLate > 0) {
                $status = 'menunggak';
            } elseif ($bill->status === 'draft') {
                $status = 'draft';
            } else {
                $status = 'terbit';
            }

            $bill->update([
                'late_fee' => $lateFee,
                'amount' => $recalculatedAmount,
                'status' => $status,
            ]);
        }
    }
}
