<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LaporanGangguan extends Model
{
    use HasFactory;

    private static ?bool $hasCoordinateColumns = null;
    private static array $columnExistenceCache = [];

    protected $fillable = [
        'kode_keluhan',
        'pelanggan_id',
        'pelapor',
        'no_hp',
        'desa_id',
        'kecamatan_id',
        'reported_by',
        'jenis_laporan',
        'prioritas',
        'judul',
        'deskripsi',
        'lokasi_text',
        'latitude',
        'longitude',
        'foto_path',
        'status_penanganan',
        'reported_at',
        'tanggal_kejadian',
        'ditangani_oleh',
        'tanggal_selesai',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'tanggal_kejadian' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public static function hasCoordinateColumns(): bool
    {
        if (self::$hasCoordinateColumns !== null) {
            return self::$hasCoordinateColumns;
        }

        try {
            if (! Schema::hasTable('laporan_gangguans')) {
                self::$hasCoordinateColumns = false;

                return self::$hasCoordinateColumns;
            }

            self::$hasCoordinateColumns = Schema::hasColumn('laporan_gangguans', 'latitude')
                && Schema::hasColumn('laporan_gangguans', 'longitude');
        } catch (Throwable $exception) {
            Log::warning('Failed to inspect laporan_gangguans coordinate columns.', [
                'error' => $exception->getMessage(),
            ]);

            self::$hasCoordinateColumns = false;
        }

        return self::$hasCoordinateColumns;
    }

    public static function hasPrioritasColumn(): bool
    {
        return self::hasColumn('prioritas');
    }

    public static function filterExistingColumns(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $column => $value) {
            if (self::hasColumn($column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    public static function hasColumn(string $column): bool
    {
        if (array_key_exists($column, self::$columnExistenceCache)) {
            return self::$columnExistenceCache[$column];
        }

        try {
            if (! Schema::hasTable('laporan_gangguans')) {
                self::$columnExistenceCache[$column] = false;

                return false;
            }

            self::$columnExistenceCache[$column] = Schema::hasColumn('laporan_gangguans', $column);
        } catch (Throwable $exception) {
            Log::warning("Failed to inspect laporan_gangguans.{$column} column.", [
                'error' => $exception->getMessage(),
            ]);

            self::$columnExistenceCache[$column] = false;
        }

        return self::$columnExistenceCache[$column];
    }
}
