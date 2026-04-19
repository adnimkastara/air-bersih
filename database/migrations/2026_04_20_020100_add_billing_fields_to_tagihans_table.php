<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->foreignId('tarif_id')->nullable()->after('meter_record_id')->constrained('tarifs')->nullOnDelete();
            $table->unsignedInteger('usage_m3')->default(0)->after('period');
            $table->decimal('base_amount', 12, 2)->default(0)->after('usage_m3');
            $table->decimal('usage_amount', 12, 2)->default(0)->after('base_amount');
            $table->decimal('late_fee', 12, 2)->default(0)->after('usage_amount');
            $table->timestamp('generated_at')->nullable()->after('late_fee');

            $table->unique(['pelanggan_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropUnique(['pelanggan_id', 'period']);
            $table->dropConstrainedForeignId('tarif_id');
            $table->dropColumn(['usage_m3', 'base_amount', 'usage_amount', 'late_fee', 'generated_at']);
        });
    }
};
