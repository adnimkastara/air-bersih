<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    private const PAYMENT_METHODS = ['tunai', 'transfer_bank', 'e_wallet'];

    public function index(Request $request)
    {
        $query = Pembayaran::with(['tagihan.pelanggan', 'petugas'])->orderByDesc('paid_at');

        if (! $request->user()->isRoot()) {
            $query->whereHas('tagihan.pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        return view('pembayaran.index', [
            'pembayarans' => $query->get(),
        ]);
    }

    public function create(Request $request)
    {
        $query = Tagihan::whereIn('status', ['draft', 'terbit', 'menunggak'])->with('pelanggan')->orderBy('due_date');

        if (! $request->user()->isRoot()) {
            $query->whereHas('pelanggan', fn ($q) => $q->where('desa_id', $request->user()->desa_id));
        }

        return view('pembayaran.create', [
            'tagihans' => $query->get(),
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

        $tagihan = Tagihan::with('pelanggan')->findOrFail($data['tagihan_id']);
        $this->abortUnlessCanAccessDesa($request, $tagihan->pelanggan?->desa_id);

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

    public function receipt(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->load('tagihan.pelanggan.desa.kecamatan', 'petugas');
        $this->abortUnlessCanAccessDesa($request, $pembayaran->tagihan?->pelanggan?->desa_id);
        $setting = AppSetting::resolveForUser($request->user());

        return view('pembayaran.receipt', [
            'pembayaran' => $pembayaran,
            'setting' => $setting,
            'printedAt' => now(),
        ]);
    }
}
