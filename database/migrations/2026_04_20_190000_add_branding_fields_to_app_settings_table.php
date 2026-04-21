<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'subjudul_aplikasi')) {
                $table->string('subjudul_aplikasi')->nullable();
            }

            if (! Schema::hasColumn('app_settings', 'logo_icon_path')) {
                $table->string('logo_icon_path')->nullable();
            }

            if (! Schema::hasColumn('app_settings', 'favicon_path')) {
                $table->string('favicon_path')->nullable();
            }

            if (! Schema::hasColumn('app_settings', 'theme_color')) {
                $table->string('theme_color', 7)->nullable();
            }

            if (! Schema::hasColumn('app_settings', 'secondary_color')) {
                $table->string('secondary_color', 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['subjudul_aplikasi', 'logo_icon_path', 'favicon_path', 'theme_color', 'secondary_color'] as $column) {
                if (Schema::hasColumn('app_settings', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
