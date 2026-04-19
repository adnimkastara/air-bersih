<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('customer_type')->nullable();
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->decimal('usage_rate', 12, 2)->default(0);
            $table->decimal('late_fee_per_day', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('effective_start')->nullable();
            $table->date('effective_end')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'customer_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
