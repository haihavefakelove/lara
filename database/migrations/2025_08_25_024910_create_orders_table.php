<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // thông tin người nhận
            $table->string('name');
            $table->string('address');
            $table->string('phone');

            // thanh toán
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('payment_method')->nullable();   // cod | momo
            $table->string('payment_status')->nullable();   // unpaid | paid | fail
            $table->string('status')->default('chờ thanh toán');

            // momo – để map lại callback/ipn
            $table->string('momo_request_id')->nullable();
            $table->string('momo_order_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
