<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'nama_ketua_direktur')) {
                $table->string('nama_ketua_direktur')->nullable();
            }

            if (! Schema::hasColumn('app_settings', 'nama_sekretaris')) {
                $table->string('nama_sekretaris')->nullable();
            }

            if (! Schema::hasColumn('app_settings', 'nama_bendahara')) {
                $table->string('nama_bendahara')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['nama_ketua_direktur', 'nama_sekretaris', 'nama_bendahara'] as $column) {
                if (Schema::hasColumn('app_settings', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
