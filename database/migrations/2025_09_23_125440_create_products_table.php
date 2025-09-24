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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
