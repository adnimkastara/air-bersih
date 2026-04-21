<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('laporan_gangguans')) {
            return;
        }

        Schema::table('laporan_gangguans', function (Blueprint $table) {
            if (! Schema::hasColumn('laporan_gangguans', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            }

            if (! Schema::hasColumn('laporan_gangguans', 'kecamatan_id')) {
                $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('laporan_gangguans')) {
            return;
        }

        Schema::table('laporan_gangguans', function (Blueprint $table) {
            foreach (['kecamatan_id', 'desa_id'] as $column) {
                if (Schema::hasColumn('laporan_gangguans', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }
        });
    }
};
