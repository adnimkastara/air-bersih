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
        'amount',
        'status',
        'due_date',
        'period',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

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
