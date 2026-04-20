<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pelanggan',
        'name',
        'email',
        'phone',
        'address',
        'dusun',
        'jenis_pelanggan',
        'nomor_meter',
        'kecamatan_id',
        'desa_id',
        'assigned_petugas_id',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
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


    public function laporanGangguans()
    {
        return $this->hasMany(LaporanGangguan::class);
    }

    public function activeTarif()
    {
        return Tarif::query()
            ->where('scope_type', Tarif::SCOPE_DESA)
            ->where('village_id', $this->desa_id)
            ->where('status', 'aktif')
            ->where(function ($query) {
                $query->where('category', $this->jenis_pelanggan)
                    ->orWhereNull('category');
            })
            ->orderByRaw('category is null')
            ->orderByDesc('effective_start')
            ->first();
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
}
