<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppSettingRequest;
use App\Models\AppSetting;
use App\Models\Kecamatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->hasAnyRole(['root', 'admin_kecamatan', 'admin_desa']), 403);

        $setting = $user->isKecamatanLevel()
            ? AppSetting::getGlobalSetting()
            : AppSetting::getOrCreateDesaSetting($user->desa_id);

        return view('settings.app.edit', [
            'setting' => $setting,
            'user' => $user,
            'globalSetting' => AppSetting::getGlobalSetting(),
        ]);
    }

    public function update(UpdateAppSettingRequest $request): RedirectResponse
    {
        $user = $request->user();

        $setting = $user->isKecamatanLevel()
            ? AppSetting::getGlobalSetting()
            : AppSetting::getOrCreateDesaSetting($user->desa_id);

        $payload = $request->validated();
        $setting->update($payload);

        if (! empty($payload['nama_kecamatan'])) {
            $targetKecamatanId = $user->kecamatan_id ?: Kecamatan::query()->value('id');
            if ($targetKecamatanId) {
                Kecamatan::whereKey($targetKecamatanId)->update(['name' => $payload['nama_kecamatan']]);
            }
        }

        return redirect()->route('settings.app.edit')->with('status', 'Setting aplikasi berhasil diperbarui.');
    }
}
