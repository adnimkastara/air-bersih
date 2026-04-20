<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class BrandingResolver
{
    public const DEFAULT_APP_NAME = 'Tirta Sejahtera';
    public const DEFAULT_SUBTITLE = 'Sistem Pengelolaan Air Bersih Desa dan Kecamatan';
    public const DEFAULT_THEME_COLOR = '#1d4ed8';

    public static function resolve(?User $user = null): array
    {
        $setting = $user
            ? AppSetting::resolveForUser($user)
            : AppSetting::where('scope_key', AppSetting::scopeKeyForGlobal())->first();

        $appName = trim((string) ($setting?->nama_aplikasi ?? '')) ?: self::DEFAULT_APP_NAME;
        $subtitle = trim((string) ($setting?->subjudul_aplikasi ?? '')) ?: self::DEFAULT_SUBTITLE;
        $themeColor = trim((string) ($setting?->theme_color ?? '')) ?: self::DEFAULT_THEME_COLOR;

        return [
            'app_name' => $appName,
            'subtitle' => $subtitle,
            'theme_color' => $themeColor,
            'logo_url' => self::resolveLogoUrl($setting?->logo_path),
            'favicon_url' => self::resolveFaviconUrl($setting?->favicon_path),
            'initials' => self::makeInitials($appName),
        ];
    }

    private static function resolveLogoUrl(?string $logoPath): ?string
    {
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->url($logoPath);
        }

        $defaultLogoPath = public_path('assets/logo/logo-main.svg');

        return file_exists($defaultLogoPath) ? asset('assets/logo/logo-main.svg') : null;
    }

    private static function resolveFaviconUrl(?string $faviconPath): string
    {
        if ($faviconPath && Storage::disk('public')->exists($faviconPath)) {
            return Storage::disk('public')->url($faviconPath);
        }

        return asset('favicon.ico');
    }

    private static function makeInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }
}
