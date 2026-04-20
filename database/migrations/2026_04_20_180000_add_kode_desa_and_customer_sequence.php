<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desas', function (Blueprint $table) {
            if (! Schema::hasColumn('desas', 'kode_desa')) {
                $table->string('kode_desa', 20)->nullable()->after('name');
                $table->unique(['kecamatan_id', 'kode_desa'], 'desas_kecamatan_kode_desa_unique');
            }
        });

        Schema::table('pelanggans', function (Blueprint $table) {
            if (! Schema::hasColumn('pelanggans', 'nomor_urut_desa')) {
                $table->unsignedInteger('nomor_urut_desa')->nullable()->after('kode_pelanggan');
                $table->unique(['desa_id', 'nomor_urut_desa'], 'pelanggans_desa_nomor_urut_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggans', 'nomor_urut_desa')) {
                $table->dropUnique('pelanggans_desa_nomor_urut_unique');
                $table->dropColumn('nomor_urut_desa');
            }
        });

        Schema::table('desas', function (Blueprint $table) {
            if (Schema::hasColumn('desas', 'kode_desa')) {
                $table->dropUnique('desas_kecamatan_kode_desa_unique');
                $table->dropColumn('kode_desa');
            }
        });
    }
};
