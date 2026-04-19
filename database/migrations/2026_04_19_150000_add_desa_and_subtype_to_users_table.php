<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->after('role_id')->constrained('desas')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'petugas_subtype')) {
                $table->string('petugas_subtype', 50)->nullable()->after('desa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'desa_id')) {
                $table->dropForeign(['desa_id']);
                $table->dropColumn('desa_id');
            }

            if (Schema::hasColumn('users', 'petugas_subtype')) {
                $table->dropColumn('petugas_subtype');
            }
        });
    }
};
