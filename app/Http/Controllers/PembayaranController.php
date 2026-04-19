<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    private const PAYMENT_METHODS = ['tunai', 'transfer_bank', 'e_wallet'];

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
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tagihan_id' => ['required', 'exists:tagihans,id'],
            'payment_method' => ['required', 'in:' . implode(',', self::PAYMENT_METHODS)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tagihan = Tagihan::findOrFail($data['tagihan_id']);

        if ($request->hasFile('proof')) {
            $data['proof_path'] = $request->file('proof')->store('payment-proofs', 'public');
        }

        $data['petugas_id'] = $request->user()->id;
        unset($data['proof']);

        $payment = Pembayaran::create($data);

        $totalPaid = $tagihan->pembayarans()->sum('amount');
        if ($totalPaid >= $tagihan->amount) {
            $tagihan->status = 'lunas';
        } else {
            $tagihan->status = now()->toDateString() > $tagihan->due_date->toDateString() ? 'menunggak' : 'terbit';
        }
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
