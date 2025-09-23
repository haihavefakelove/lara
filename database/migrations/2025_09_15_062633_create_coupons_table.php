<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();                 // Mã
            $table->enum('type', ['percent', 'fixed']);           // Loại: % hoặc số tiền
            $table->decimal('value', 12, 2);                      // Giá trị
            $table->unsignedInteger('max_uses')->nullable();      // Giới hạn lượt dùng (null = không giới hạn)
            $table->unsignedInteger('used')->default(0);          // Đã dùng
            $table->decimal('min_order', 12, 2)->nullable();      // Đơn tối thiểu (nếu có)
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
