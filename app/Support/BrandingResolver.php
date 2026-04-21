<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BrandingResolver
{
    public const DEFAULT_APP_NAME = 'Tirta Sejahtera';
    public const DEFAULT_SUBTITLE = 'Sistem Pengelolaan Air Bersih Desa dan Kecamatan';
    public const DEFAULT_PRIMARY_COLOR = '#1d4ed8';
    public const DEFAULT_SECONDARY_COLOR = '#14b8a6';

    private const DEFAULT_LOGO_PATH = 'assets/logo/logo-main.svg';
    private const DEFAULT_LOGO_ICON_PATH = 'assets/logo/logo-icon.svg';
    private const DEFAULT_FAVICON_PATH = 'favicon.ico';

    public static function resolve(?User $user = null): array
    {
        $setting = null;

        if (self::canReadDatabaseSetting()) {
            try {
                $setting = $user
                    ? AppSetting::resolveForUser($user)
                    : AppSetting::where('scope_key', AppSetting::scopeKeyForGlobal())->first();
            } catch (Throwable) {
                $setting = null;
            }
        }

        $appName = trim((string) ($setting?->nama_aplikasi ?? '')) ?: self::DEFAULT_APP_NAME;
        $subtitle = trim((string) ($setting?->subjudul_aplikasi ?? '')) ?: self::DEFAULT_SUBTITLE;
        $primaryColor = trim((string) ($setting?->theme_color ?? '')) ?: self::DEFAULT_PRIMARY_COLOR;
        $secondaryColor = trim((string) ($setting?->secondary_color ?? '')) ?: self::DEFAULT_SECONDARY_COLOR;
        $logoUrl = self::resolveImageUrl($setting?->logo_path, self::DEFAULT_LOGO_PATH);
        $logoIconUrl = self::resolveImageUrl($setting?->logo_icon_path, self::DEFAULT_LOGO_ICON_PATH) ?? $logoUrl;

        return [
            'app_name' => $appName,
            'app_subtitle' => $subtitle,
            'subtitle' => $subtitle,
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'theme_color' => $primaryColor,
            'logo_url' => $logoUrl,
            'logo_icon_url' => $logoIconUrl,
            'favicon_url' => self::resolveImageUrl($setting?->favicon_path, self::DEFAULT_FAVICON_PATH),
            'initials' => self::makeInitials($appName),
            'logo_path' => $setting?->logo_path,
            'logo_icon_path' => $setting?->logo_icon_path,
            'favicon_path' => $setting?->favicon_path,
        ];
    }

    public static function resolveImageUrl(?string $pathFromDb, ?string $defaultPublicPath = null): ?string
    {
        $uploadedUrl = self::resolveUploadedAssetUrl($pathFromDb);
        if ($uploadedUrl !== null) {
            return $uploadedUrl;
        }

        if (! $defaultPublicPath) {
            return null;
        }

        return File::exists(public_path($defaultPublicPath))
            ? asset($defaultPublicPath)
            : null;
    }

    public static function resolveUploadedAssetUrl(?string $pathFromDb): ?string
    {
        if (! filled($pathFromDb)) {
            return null;
        }

        $normalized = str_replace('\\', '/', trim((string) $pathFromDb));
        if ($normalized === '') {
            return null;
        }

        if (Str::startsWith($normalized, ['http://', 'https://', '//'])) {
            return $normalized;
        }

        $normalized = ltrim($normalized, '/');

        $candidates = array_values(array_unique([
            $normalized,
            Str::replaceFirst('public/', '', $normalized),
            Str::replaceFirst('storage/', '', $normalized),
            Str::startsWith($normalized, 'uploads/')
                ? Str::replaceFirst('uploads/', '', $normalized)
                : null,
        ]));

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }

            if (Storage::disk('public')->exists($candidate)) {
                return self::resolvePublicDiskUrl($candidate);
            }
        }

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }

            if (File::exists(public_path($candidate))) {
                return asset($candidate);
            }

            if (File::exists(public_path('uploads/' . $candidate))) {
                return asset('uploads/' . $candidate);
            }
        }

        return null;
    }

    private static function resolvePublicDiskUrl(string $path): string
    {
        $hasStorageLink = File::exists(public_path('storage'));

        if ($hasStorageLink) {
            return Storage::disk('public')->url($path);
        }

        return route('branding.media', ['path' => $path]);
    }

    private static function makeInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    private static function canReadDatabaseSetting(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        try {
            return Schema::hasTable('app_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
