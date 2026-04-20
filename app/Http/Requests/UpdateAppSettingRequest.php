<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['root', 'admin_kecamatan', 'admin_desa']);
    }

    public function rules(): array
    {
        return [
            'nama_kecamatan' => ['nullable', 'string', 'max:255'],
            'nama_unit_pengelola' => ['nullable', 'string', 'max:255'],
            'tipe_pengelola' => ['nullable', 'string', 'max:100'],
            'nama_aplikasi' => ['nullable', 'string', 'max:255'],
            'subjudul_aplikasi' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico', 'max:1024'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'nama_ketua_direktur' => ['nullable', 'string', 'max:255'],
            'nama_sekretaris' => ['nullable', 'string', 'max:255'],
            'nama_bendahara' => ['nullable', 'string', 'max:255'],
        ];
    }
}
