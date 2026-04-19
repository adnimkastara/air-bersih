<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_type',
        'base_rate',
        'usage_rate',
        'late_fee_per_day',
        'is_active',
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
        return $query->where('is_active', true);
    }
}
