<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            if (! Schema::hasColumn('pelanggans', 'kode_pelanggan')) {
                $table->string('kode_pelanggan')->nullable()->after('id')->unique();
            }

            if (! Schema::hasColumn('pelanggans', 'dusun')) {
                $table->string('dusun')->nullable()->after('address');
            }

            if (! Schema::hasColumn('pelanggans', 'jenis_pelanggan')) {
                $table->string('jenis_pelanggan')->nullable()->after('dusun');
            }

            if (! Schema::hasColumn('pelanggans', 'nomor_meter')) {
                $table->string('nomor_meter')->nullable()->after('jenis_pelanggan')->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggans', 'nomor_meter')) {
                $table->dropUnique('pelanggans_nomor_meter_unique');
                $table->dropColumn('nomor_meter');
            }

            if (Schema::hasColumn('pelanggans', 'jenis_pelanggan')) {
                $table->dropColumn('jenis_pelanggan');
            }

            if (Schema::hasColumn('pelanggans', 'dusun')) {
                $table->dropColumn('dusun');
            }

            if (Schema::hasColumn('pelanggans', 'kode_pelanggan')) {
                $table->dropUnique('pelanggans_kode_pelanggan_unique');
                $table->dropColumn('kode_pelanggan');
            }
        });
    }
};
