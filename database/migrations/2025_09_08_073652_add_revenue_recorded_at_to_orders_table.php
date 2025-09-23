<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_revenue_recorded_at_to_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('revenue_recorded_at')->nullable()->after('shipping_status');
        });
    }
    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('revenue_recorded_at');
        });
    }
};
