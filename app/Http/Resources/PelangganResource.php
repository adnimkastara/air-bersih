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
            'phone' => $this->phone,
            'email' => $this->email,
            'desa_id' => $this->desa_id,
            'desa_name' => $this->desa?->name,
            'kecamatan_id' => $this->kecamatan_id,
            'kecamatan_name' => $this->kecamatan?->name,
            'assigned_petugas_id' => $this->assigned_petugas_id,
            'assigned_petugas_name' => $this->assignedPetugas?->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
        ];
    }
}
