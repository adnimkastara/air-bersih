<?php

namespace App\Http\Controllers;

use App\Models\MeterRecord;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TagihanController extends Controller
{
    public function index()
    {
        return view('tagihan.index', [
            'tagihans' => Tagihan::with(['pelanggan', 'meterRecord'])->orderByDesc('created_at')->get(),
        ]);
    }

    public function generate(Request $request)
    {
        $meterRecords = MeterRecord::where('recorded_at', '>=', Carbon::now()->subMonth())->get();

        $created = 0;

        foreach ($meterRecords as $record) {
            $exists = Tagihan::where('meter_record_id', $record->id)->exists();
            if ($exists) {
                continue;
            }

            $consumption = $record->meter_current_month - $record->meter_previous_month;
            if ($consumption <= 0) {
                continue;
            }

            $amount = $consumption * 1000;
            $period = Carbon::parse($record->recorded_at)->format('Y-m');
            $dueDate = Carbon::parse($record->recorded_at)->endOfMonth();

            Tagihan::create([
                'pelanggan_id' => $record->pelanggan_id,
                'meter_record_id' => $record->id,
                'amount' => $amount,
                'status' => 'draft',
                'due_date' => $dueDate,
                'period' => $period,
            ]);

            $created++;
        }

        $this->logActivity($request, 'generate_tagihan', Tagihan::class, null, "Menghasilkan $created tagihan baru.");

        return redirect()->route('tagihan.index')->with('status', "Berhasil membuat $created tagihan baru.");
    }

    public function publish(Request $request, Tagihan $tagihan)
    {
        $tagihan->status = 'terbit';
        $tagihan->save();
        $this->logActivity($request, 'publish_tagihan', Tagihan::class, $tagihan->id, "Menerbitkan tagihan {$tagihan->id}");

        return back()->with('status', 'Tagihan berhasil diterbitkan.');
    }
}
