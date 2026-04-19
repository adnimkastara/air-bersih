<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_records', function (Blueprint $table) {
            if (! Schema::hasColumn('meter_records', 'meter_photo_path')) {
                $table->string('meter_photo_path')->nullable()->after('recorded_at');
            }

            if (! Schema::hasColumn('meter_records', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'terverifikasi', 'ditolak'])
                    ->default('pending')
                    ->after('meter_photo_path');
            }

            if (! Schema::hasColumn('meter_records', 'is_anomaly')) {
                $table->boolean('is_anomaly')->default(false)->after('verification_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meter_records', function (Blueprint $table) {
            if (Schema::hasColumn('meter_records', 'is_anomaly')) {
                $table->dropColumn('is_anomaly');
            }

            if (Schema::hasColumn('meter_records', 'verification_status')) {
                $table->dropColumn('verification_status');
            }

            if (Schema::hasColumn('meter_records', 'meter_photo_path')) {
                $table->dropColumn('meter_photo_path');
            }
        });
    }
};
