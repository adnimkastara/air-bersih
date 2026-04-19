<?php

namespace App\Models;

use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\MeterRecord;
use App\Models\Pembayaran;
use App\Models\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'desa_id',
        'petugas_subtype',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function meterRecords()
    {
        return $this->hasMany(MeterRecord::class, 'petugas_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'petugas_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->name, $roles, true);
    }

    public function isAdminDesa(): bool
    {
        return $this->hasRole('admin_desa');
    }

    public function isPetugasLapangan(): bool
    {
        return $this->hasRole('petugas_lapangan');
    }

    public function isRoot(): bool
    {
        return $this->hasRole('root');
    }

    public function isPencatatMeter(): bool
    {
        return $this->isPetugasLapangan() && $this->petugas_subtype === 'pencatat_meter';
    }

    public function isPenagihIuran(): bool
    {
        return $this->isPetugasLapangan() && $this->petugas_subtype === 'penagih_iuran';
    }

    public function canManageUsers(): bool
    {
        return $this->isRoot() || $this->isAdminDesa();
    }

    public function canAccessGlobalMaster(): bool
    {
        return $this->isRoot();
    }

    public function canAccessFinancialReport(): bool
    {
        return $this->isRoot() || $this->isAdminDesa();
    }

    public function canAccessVillage(int|string|null $desaId): bool
    {
        if ($this->isRoot()) {
            return true;
        }

        return (int) $this->desa_id === (int) $desaId;
    }
}
