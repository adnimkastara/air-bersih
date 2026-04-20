<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    public const SCOPE_DESA = 'desa';
    public const SCOPE_KECAMATAN = 'kecamatan';

    protected $fillable = [
        'scope_type',
        'village_id',
        'name',
        'customer_type',
        'category',
        'abonemen',
        'tarif_dasar',
        'tarif_per_m3',
        'base_rate',
        'usage_rate',
        'late_fee_per_day',
        'is_active',
        'status',
        'effective_start',
        'effective_end',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_start' => 'date',
        'effective_end' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'village_id');
    }
}
