<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppSettingRequest;
use App\Models\AppSetting;
use App\Models\Kecamatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $payload = $request->safe()->except(['logo', 'logo_icon', 'favicon']);

        if ($request->hasFile('logo')) {
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $payload['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('logo_icon')) {
            if ($setting->logo_icon_path && Storage::disk('public')->exists($setting->logo_icon_path)) {
                Storage::disk('public')->delete($setting->logo_icon_path);
            }

            $payload['logo_icon_path'] = $request->file('logo_icon')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon_path && Storage::disk('public')->exists($setting->favicon_path)) {
                Storage::disk('public')->delete($setting->favicon_path);
            }

            $payload['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

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
