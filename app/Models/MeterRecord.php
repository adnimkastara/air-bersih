<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggan_id',
        'petugas_id',
        'meter_previous_month',
        'meter_current_month',
        'recorded_at',
        'gps_latitude',
        'gps_longitude',
        'gps_recorded_at',
        'meter_photo_path',
        'verification_status',
        'is_anomaly',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'gps_recorded_at' => 'datetime',
        'gps_latitude' => 'decimal:7',
        'gps_longitude' => 'decimal:7',
        'is_anomaly' => 'boolean',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
