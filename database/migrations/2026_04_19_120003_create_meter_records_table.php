<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('meter_previous_month');
            $table->unsignedBigInteger('meter_current_month');
            $table->date('recorded_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_records');
    }
};
