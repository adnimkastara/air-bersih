<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarifs', function (Blueprint $table) {
            $table->string('scope_type')->default('desa')->after('id');
            $table->foreignId('village_id')->nullable()->after('scope_type')->constrained('desas')->nullOnDelete();
            $table->string('category')->nullable()->after('customer_type');
            $table->decimal('abonemen', 12, 2)->default(0)->after('category');
            $table->decimal('tarif_dasar', 12, 2)->default(0)->after('abonemen');
            $table->decimal('tarif_per_m3', 12, 2)->default(0)->after('tarif_dasar');
            $table->string('status')->default('aktif')->after('is_active');

            $table->index(['scope_type', 'status']);
            $table->index(['scope_type', 'village_id', 'status']);
        });

        DB::table('tarifs')->update([
            'category' => DB::raw('COALESCE(customer_type, category)'),
            'abonemen' => DB::raw('base_rate'),
            'tarif_dasar' => DB::raw('base_rate'),
            'tarif_per_m3' => DB::raw('usage_rate'),
            'status' => DB::raw("CASE WHEN is_active = 1 THEN 'aktif' ELSE 'nonaktif' END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('tarifs', function (Blueprint $table) {
            $table->dropIndex(['scope_type', 'status']);
            $table->dropIndex(['scope_type', 'village_id', 'status']);
            $table->dropConstrainedForeignId('village_id');
            $table->dropColumn(['scope_type', 'category', 'abonemen', 'tarif_dasar', 'tarif_per_m3', 'status']);
        });
    }
};
