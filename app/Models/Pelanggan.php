<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'kecamatan_id',
        'desa_id',
        'assigned_petugas_id',
        'latitude',
        'longitude',
        'status',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function assignedPetugas()
    {
        return $this->belongsTo(User::class, 'assigned_petugas_id');
    }

    public function meterRecords()
    {
        return $this->hasMany(MeterRecord::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
}
