<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    use HasFactory;

    protected $fillable = [
        'kecamatan_id',
        'name',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function districtBillings()
    {
        return $this->hasMany(DistrictBilling::class);
    }
}

