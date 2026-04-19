<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'totalPelanggan' => Pelanggan::count(),
            'totalTagihan' => Tagihan::count(),
            'totalPembayaran' => Pembayaran::count(),
            'totalTunggakan' => Tagihan::where('status', 'menunggak')->count(),
            'totalGangguan' => ActivityLog::where('action', 'like', '%anomali%')->count(),
        ]);
    }
}
