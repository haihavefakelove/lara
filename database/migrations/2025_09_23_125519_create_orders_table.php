<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            
            // Thông tin người nhận
            $table->string('name');
            $table->string('phone', 20);
            $table->string('address', 255);
            
            // Thanh toán
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->string('payment_method')->nullable(); // cod | momo | bank_transfer
            $table->string('payment_status')->nullable(); // unpaid | paid | fail
            $table->string('status')->default('chờ thanh toán');
            $table->string('shipping_status')->default('not_shipped'); // not_shipped | packaged | shipping | completed | cancelled
            
            // MoMo fields
            $table->string('momo_request_id')->nullable();
            $table->string('momo_order_id')->nullable();
            
            // Report và Mail flags
            $table->boolean('is_reported')->default(false);
            $table->timestamp('revenue_recorded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('review_mail_sent_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
