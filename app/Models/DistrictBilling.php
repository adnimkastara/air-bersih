<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'desa_id',
        'period',
        'total_usage_m3',
        'tarif_id',
        'tarif_per_m3',
        'total_setoran',
        'status',
        'due_date',
        'generated_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'generated_at' => 'datetime',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function tarif()
    {
        return $this->belongsTo(Tarif::class);
    }
}
