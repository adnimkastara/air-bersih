<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppSettingRequest;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->hasAnyRole(['root', 'admin_desa']), 403);

        $setting = $user->isRoot()
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

        $setting = $user->isRoot()
            ? AppSetting::getGlobalSetting()
            : AppSetting::getOrCreateDesaSetting($user->desa_id);

        $setting->update($request->validated());

        return redirect()->route('settings.app.edit')->with('status', 'Setting aplikasi berhasil diperbarui.');
    }
}
