<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PelangganResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_pelanggan' => $this->kode_pelanggan,
            'name' => $this->name,
            'address' => $this->address,
            'desa_id' => $this->desa_id,
            'kecamatan_id' => $this->kecamatan_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
        ];
    }
}
