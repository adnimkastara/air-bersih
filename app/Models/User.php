<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\ActivityLog;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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

    public function isAdminKecamatan(): bool
    {
        return $this->hasAnyRole(['admin_kecamatan', 'root']);
    }

    public function isAdminDesa(): bool
    {
        return $this->hasAnyRole(['admin_desa', 'root']);
    }

    public function isPetugasLapangan(): bool
    {
        return $this->hasAnyRole(['petugas_lapangan', 'root']);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'admin_kecamatan', 'admin_desa', 'root']);
    }

    public function isRoot(): bool
    {
        return $this->role?->name === 'root';
    }
}
