<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'kecamatan_id')) {
                $table->foreignId('kecamatan_id')->nullable()->after('desa_id')->constrained('kecamatans')->nullOnDelete();
            }
        });

        Schema::table('desas', function (Blueprint $table) {
            if (! Schema::hasColumn('desas', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('name');
            }

            if (! Schema::hasColumn('desas', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        Schema::table('kecamatans', function (Blueprint $table) {
            if (! Schema::hasColumn('kecamatans', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('name');
            }

            if (! Schema::hasColumn('kecamatans', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        Schema::table('meter_records', function (Blueprint $table) {
            if (! Schema::hasColumn('meter_records', 'gps_latitude')) {
                $table->decimal('gps_latitude', 10, 7)->nullable()->after('recorded_at');
            }

            if (! Schema::hasColumn('meter_records', 'gps_longitude')) {
                $table->decimal('gps_longitude', 10, 7)->nullable()->after('gps_latitude');
            }

            if (! Schema::hasColumn('meter_records', 'gps_recorded_at')) {
                $table->timestamp('gps_recorded_at')->nullable()->after('gps_longitude');
            }
        });

        Schema::table('laporan_gangguans', function (Blueprint $table) {
            if (! Schema::hasColumn('laporan_gangguans', 'kode_keluhan')) {
                $table->string('kode_keluhan')->nullable()->after('id');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->after('pelanggan_id')->constrained('desas')->nullOnDelete();
            }

            if (! Schema::hasColumn('laporan_gangguans', 'kecamatan_id')) {
                $table->foreignId('kecamatan_id')->nullable()->after('desa_id')->constrained('kecamatans')->nullOnDelete();
            }

            if (! Schema::hasColumn('laporan_gangguans', 'prioritas')) {
                $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang')->after('jenis_laporan');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'lokasi_text')) {
                $table->string('lokasi_text')->nullable()->after('deskripsi');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('lokasi_text');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'tanggal_kejadian')) {
                $table->timestamp('tanggal_kejadian')->nullable()->after('longitude');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'ditangani_oleh')) {
                $table->foreignId('ditangani_oleh')->nullable()->after('status_penanganan')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('laporan_gangguans', 'tanggal_selesai')) {
                $table->timestamp('tanggal_selesai')->nullable()->after('ditangani_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('laporan_gangguans', function (Blueprint $table) {
            foreach (['ditangani_oleh', 'kecamatan_id', 'desa_id'] as $foreignColumn) {
                if (Schema::hasColumn('laporan_gangguans', $foreignColumn)) {
                    $table->dropForeign([$foreignColumn]);
                }
            }

            $table->dropColumn([
                'kode_keluhan',
                'desa_id',
                'kecamatan_id',
                'prioritas',
                'lokasi_text',
                'latitude',
                'longitude',
                'tanggal_kejadian',
                'ditangani_oleh',
                'tanggal_selesai',
            ]);
        });

        Schema::table('meter_records', function (Blueprint $table) {
            $table->dropColumn(['gps_latitude', 'gps_longitude', 'gps_recorded_at']);
        });

        Schema::table('kecamatans', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('desas', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
            $table->dropColumn('kecamatan_id');
        });
    }
};
