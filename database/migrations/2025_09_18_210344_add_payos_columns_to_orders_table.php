<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm các cột PayOS vào bảng orders:
     * - payos_order_code: mã đơn gửi sang PayOS (unique theo hệ thống của bạn), để index để tra nhanh.
     * - payos_checkout_url: URL thanh toán PayOS (nếu cần hiển thị lại).
     * - paid_at: thời điểm ghi nhận đã thanh toán (tuỳ bạn đã có hay chưa).
     */
    public function up(): void
    {
        // Chỉ thêm nếu cột chưa tồn tại (tránh lỗi khi migrate nhiều lần / môi trường khác nhau)
        if (
            !Schema::hasColumn('orders', 'payos_order_code') ||
            !Schema::hasColumn('orders', 'payos_checkout_url') ||
            !Schema::hasColumn('orders', 'paid_at')
        ) {
            Schema::table('orders', function (Blueprint $table) {
                // Đặt sau momo_order_id (nếu cột đó có). Nếu không có cũng không sao.
                if (!Schema::hasColumn('orders', 'payos_order_code')) {
                    $table->string('payos_order_code')->nullable()->index()->after('momo_order_id');
                }

                if (!Schema::hasColumn('orders', 'payos_checkout_url')) {
                    $table->string('payos_checkout_url')->nullable()->after('payos_order_code');
                }

                if (!Schema::hasColumn('orders', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('payos_checkout_url');
                }
            });
        }
    }

    /**
     * Rollback: xoá các cột đã thêm (nếu đang tồn tại).
     */
    public function down(): void
    {
        if (
            Schema::hasColumn('orders', 'payos_order_code') ||
            Schema::hasColumn('orders', 'payos_checkout_url') ||
            Schema::hasColumn('orders', 'paid_at')
        ) {
            Schema::table('orders', function (Blueprint $table) {
                // Xoá index trước khi xoá cột (nếu có)
                if (Schema::hasColumn('orders', 'payos_order_code')) {
                    // Tên index do Laravel tạo là "orders_payos_order_code_index"
                    $table->dropIndex(['payos_order_code']);
                    $table->dropColumn('payos_order_code');
                }

                if (Schema::hasColumn('orders', 'payos_checkout_url')) {
                    $table->dropColumn('payos_checkout_url');
                }

                if (Schema::hasColumn('orders', 'paid_at')) {
                    $table->dropColumn('paid_at');
                }
            });
        }
    }
};
