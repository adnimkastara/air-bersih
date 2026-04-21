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
            if (! Schema::hasColumn('laporan_gangguans', 'latitude')) {
                $column = $table->decimal('latitude', 10, 7)->nullable();
                if (Schema::hasColumn('laporan_gangguans', 'lokasi_text')) {
                    $column->after('lokasi_text');
                }
            }

            if (! Schema::hasColumn('laporan_gangguans', 'longitude')) {
                $column = $table->decimal('longitude', 10, 7)->nullable();
                if (Schema::hasColumn('laporan_gangguans', 'latitude')) {
                    $column->after('latitude');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('laporan_gangguans')) {
            return;
        }

        Schema::table('laporan_gangguans', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('laporan_gangguans', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
