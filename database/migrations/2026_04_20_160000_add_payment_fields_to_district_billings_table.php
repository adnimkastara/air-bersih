<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('district_billings', function (Blueprint $table) {
            $table->decimal('paid_amount', 14, 2)->default(0)->after('total_setoran');
            $table->date('paid_at')->nullable()->after('due_date');
            $table->string('payment_method', 40)->nullable()->after('paid_at');
            $table->text('payment_notes')->nullable()->after('payment_method');
            $table->enum('payment_status', ['belum_bayar', 'sebagian', 'lunas'])->default('belum_bayar')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('district_billings', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'paid_at', 'payment_method', 'payment_notes', 'payment_status']);
        });
    }
};
