<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_daily_reports_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('day')->unique();
            $table->unsignedBigInteger('orders_count')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('daily_reports');
    }
};
