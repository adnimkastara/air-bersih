<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('laporan_gangguans')) {
            return;
        }

        if (! Schema::hasColumn('laporan_gangguans', 'prioritas')) {
            Schema::table('laporan_gangguans', function (Blueprint $table) {
                $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])
                    ->default('sedang')
                    ->after('jenis_laporan');
            });
        }

        DB::table('laporan_gangguans')
            ->whereNull('prioritas')
            ->update(['prioritas' => 'sedang']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('laporan_gangguans') || ! Schema::hasColumn('laporan_gangguans', 'prioritas')) {
            return;
        }

        Schema::table('laporan_gangguans', function (Blueprint $table) {
            $table->dropColumn('prioritas');
        });
    }
};
