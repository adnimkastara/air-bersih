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
            if (! Schema::hasColumn('laporan_gangguans', 'pelapor')) {
                $column = $table->string('pelapor')->nullable();
                if (Schema::hasColumn('laporan_gangguans', 'pelanggan_id')) {
                    $column->after('pelanggan_id');
                }
            }

            if (! Schema::hasColumn('laporan_gangguans', 'no_hp')) {
                $column = $table->string('no_hp')->nullable();
                if (Schema::hasColumn('laporan_gangguans', 'pelapor')) {
                    $column->after('pelapor');
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
            $columns = [];

            if (Schema::hasColumn('laporan_gangguans', 'pelapor')) {
                $columns[] = 'pelapor';
            }

            if (Schema::hasColumn('laporan_gangguans', 'no_hp')) {
                $columns[] = 'no_hp';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
