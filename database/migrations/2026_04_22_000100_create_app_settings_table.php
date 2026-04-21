<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 20); // global | desa
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->string('scope_key')->unique();
            $table->string('nama_kecamatan')->nullable();
            $table->string('nama_unit_pengelola')->nullable();
            $table->string('tipe_pengelola')->nullable();
            $table->string('app_name')->nullable();
            $table->string('nama_aplikasi')->nullable();
            $table->string('subjudul_aplikasi')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('logo_icon_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('theme_color', 7)->nullable();
            $table->string('secondary_color', 7)->nullable();
            $table->string('alamat')->nullable();
            $table->string('kontak')->nullable();
            $table->string('nama_ketua_direktur')->nullable();
            $table->string('nama_sekretaris')->nullable();
            $table->string('nama_bendahara')->nullable();
            $table->timestamps();

            $table->index(['scope_type', 'desa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
