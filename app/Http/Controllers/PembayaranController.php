<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('pembayaran.index', [
            'pembayarans' => Pembayaran::with(['tagihan.pelanggan', 'petugas'])->orderByDesc('paid_at')->get(),
        ]);
    }

    public function create()
    {
        return view('pembayaran.create', [
            'tagihans' => Tagihan::whereIn('status', ['draft', 'terbit', 'menunggak'])->with('pelanggan')->orderBy('due_date')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tagihan_id' => ['required', 'exists:tagihans,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tagihan = Tagihan::findOrFail($data['tagihan_id']);
        $data['petugas_id'] = $request->user()->id;
        $payment = Pembayaran::create($data);

        $totalPaid = $tagihan->pembayarans()->sum('amount');
        $status = $totalPaid >= $tagihan->amount ? 'lunas' : ($data['paid_at'] > $tagihan->due_date ? 'menunggak' : 'terbit');
        $tagihan->status = $status;
        $tagihan->save();

        $this->logActivity($request, 'create_pembayaran', Pembayaran::class, $payment->id, "Membuat pembayaran tagihan {$tagihan->id}");

        return redirect()->route('pembayaran.index')->with('status', 'Pembayaran berhasil dicatat.');
    }

    public function receipt(Pembayaran $pembayaran)
    {
        return view('pembayaran.receipt', [
            'pembayaran' => $pembayaran->load('tagihan.pelanggan', 'petugas'),
        ]);
    }
}
