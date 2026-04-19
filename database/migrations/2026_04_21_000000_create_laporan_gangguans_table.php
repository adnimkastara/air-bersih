<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_gangguans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('jenis_laporan', ['gangguan', 'keluhan'])->default('gangguan');
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('foto_path')->nullable();
            $table->enum('status_penanganan', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();

            $table->index(['jenis_laporan', 'status_penanganan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_gangguans');
    }
};
