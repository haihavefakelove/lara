<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->unsignedBigInteger('category_id');
$table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

        $table->id();
        $table->string('name');
        $table->string('brand');
        $table->decimal('price', 10, 2);
        $table->integer('quantity');
        $table->string('sku')->unique();
        $table->string('volume')->nullable();
        $table->string('shade')->nullable();
        $table->date('expiry_date')->nullable();
        $table->string('origin')->nullable();
        $table->string('skin_type')->nullable();
        $table->text('features')->nullable();
        $table->text('ingredients')->nullable();
        $table->text('usage')->nullable();
        $table->text('description')->nullable();
        $table->string('image_url')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
