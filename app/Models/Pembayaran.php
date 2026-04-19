<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'petugas_id',
        'payment_method',
        'amount',
        'paid_at',
        'proof_path',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
