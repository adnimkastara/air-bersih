<?php

namespace App\Models;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AppSetting extends Model
{
    use HasFactory;

    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_DESA = 'desa';

    protected $fillable = [
        'scope_type',
        'desa_id',
        'scope_key',
        'nama_kecamatan',
        'nama_unit_pengelola',
        'tipe_pengelola',
        'official_name',
        'nama_aplikasi',
        'logo_path',
        'logo_icon_path',
        'favicon_path',
        'subjudul_aplikasi',
        'theme_color',
        'secondary_color',
        'alamat',
        'kontak',
        'nama_ketua_direktur',
        'nama_sekretaris',
        'nama_bendahara',
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public static function scopeKeyForGlobal(): string
    {
        return self::SCOPE_GLOBAL;
    }

    public static function scopeKeyForDesa(int|string $desaId): string
    {
        return self::SCOPE_DESA.':'.$desaId;
    }

    public static function getGlobalSetting(): self
    {
        if (! self::canQueryTable()) {
            return new self(['scope_type' => self::SCOPE_GLOBAL, 'scope_key' => self::scopeKeyForGlobal()]);
        }

        return self::firstOrCreate(
            ['scope_key' => self::scopeKeyForGlobal()],
            ['scope_type' => self::SCOPE_GLOBAL]
        );
    }

    public static function getOrCreateDesaSetting(int|string $desaId): self
    {
        if (! self::canQueryTable()) {
            return new self([
                'scope_type' => self::SCOPE_DESA,
                'scope_key' => self::scopeKeyForDesa($desaId),
                'desa_id' => $desaId,
            ]);
        }

        return self::firstOrCreate(
            ['scope_key' => self::scopeKeyForDesa($desaId)],
            ['scope_type' => self::SCOPE_DESA, 'desa_id' => $desaId]
        );
    }

    public static function resolveForUser(User $user): ?self
    {
        if (! self::canQueryTable()) {
            return null;
        }

        if ($user->isKecamatanLevel()) {
            return self::where('scope_key', self::scopeKeyForGlobal())->first();
        }

        $desaSetting = self::where('scope_key', self::scopeKeyForDesa($user->desa_id))->first();
        if ($desaSetting) {
            return $desaSetting;
        }

        return self::where('scope_key', self::scopeKeyForGlobal())->first();
    }

    private static function canQueryTable(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
