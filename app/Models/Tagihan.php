<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggan_id',
        'meter_record_id',
        'tarif_id',
        'amount',
        'status',
        'due_date',
        'period',
        'usage_m3',
        'base_amount',
        'usage_amount',
        'late_fee',
        'generated_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'generated_at' => 'datetime',
    ];

    public function tarif()
    {
        return $this->belongsTo(Tarif::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function meterRecord()
    {
        return $this->belongsTo(MeterRecord::class, 'meter_record_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
