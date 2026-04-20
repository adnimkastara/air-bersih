<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('nama_ketua_direktur')->nullable()->after('kontak');
            $table->string('nama_sekretaris')->nullable()->after('nama_ketua_direktur');
            $table->string('nama_bendahara')->nullable()->after('nama_sekretaris');
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['nama_ketua_direktur', 'nama_sekretaris', 'nama_bendahara']);
        });
    }
};
