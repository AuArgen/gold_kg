<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Сначала удаляем старую таблицу, если она существует
        Schema::dropIfExists('products');

        // Создаем новую таблицу с правильной структурой
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Наш внутренний автоинкрементный ID
            $table->unsignedBigInteger('product_id')->unique(); // ID с Wildberries

            $table->string('url')->nullable();
            $table->string('imageUrl')->nullable();
            $table->string('brand')->nullable();
            $table->string('name');
            $table->string('title');
            $table->integer('currentPrice');
            $table->integer('oldPrice')->nullable();
            $table->integer('discountPercentage')->nullable();
            $table->boolean('isNew')->default(false);
            $table->boolean('isGoodPrice')->default(false);
            $table->string('actionPromotion')->nullable();
            $table->float('rating')->nullable();
            $table->integer('reviewCount')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // При откате можно просто удалить таблицу
        Schema::dropIfExists('products');
    }
};
