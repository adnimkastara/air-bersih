<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'petugas_subtype')) {
                $table->string('petugas_subtype', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

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
