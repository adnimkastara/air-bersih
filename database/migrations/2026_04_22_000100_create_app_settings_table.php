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
            $table->string('nama_aplikasi')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('alamat')->nullable();
            $table->string('kontak')->nullable();
            $table->timestamps();

            $table->index(['scope_type', 'desa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
