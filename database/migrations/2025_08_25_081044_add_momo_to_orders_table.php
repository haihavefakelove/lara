<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending'); // pending|paid|failed
            }
            if (!Schema::hasColumn('orders', 'momo_request_id')) {
                $table->string('momo_request_id')->nullable();
            }
            if (!Schema::hasColumn('orders', 'momo_order_id')) {
                $table->string('momo_order_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('orders', 'momo_request_id')) {
                $table->dropColumn('momo_request_id');
            }
            if (Schema::hasColumn('orders', 'momo_order_id')) {
                $table->dropColumn('momo_order_id');
            }
        });
    }
};
