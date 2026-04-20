<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_gangguans', function (Blueprint $table) {
            if (! Schema::hasColumn('laporan_gangguans', 'pelapor')) {
                $table->string('pelapor')->nullable()->after('pelanggan_id');
            }

            if (! Schema::hasColumn('laporan_gangguans', 'no_hp')) {
                $table->string('no_hp')->nullable()->after('pelapor');
            }
        });
    }

    public function down(): void
    {
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
