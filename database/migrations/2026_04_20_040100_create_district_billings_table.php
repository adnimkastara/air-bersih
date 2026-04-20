<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('district_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desas')->cascadeOnDelete();
            $table->string('period');
            $table->unsignedInteger('total_usage_m3')->default(0);
            $table->foreignId('tarif_id')->nullable()->constrained('tarifs')->nullOnDelete();
            $table->decimal('tarif_per_m3', 12, 2)->default(0);
            $table->decimal('total_setoran', 14, 2)->default(0);
            $table->enum('status', ['draft', 'terbit', 'lunas'])->default('draft');
            $table->date('due_date')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['desa_id', 'period']);
            $table->index(['period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_billings');
    }
};
