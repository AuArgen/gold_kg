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
        Schema::create('gold_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gold_id'); // Ссылка на таблицу 'gold' (вес)

            // Цены, умноженные на 100 (хранятся в копейках)
            $table->unsignedBigInteger('sale_kopecks');      // "Сатуу баасы, сом" (Цена продажи)
            $table->unsignedBigInteger('buy_in_kopecks');    // "Кайра сатып алуу баасы, сом" (Цена выкупа)

            // ПОЛЯ РАЗНИЦЫ ИЗМЕНЕНЫ НА ЗНАКОВЫЙ BigInteger:
            // Это позволяет хранить разницу (положительную или отрицательную).
            $table->bigInteger('difference_sale_kopecks')->nullable();
            $table->bigInteger('difference_buy_in_kopecks')->nullable();

            $table->date('public_date'); // Дата публикации

            // Уникальный индекс для предотвращения дублирования цен за один и тот же вес в одну дату
            $table->unique(['gold_id', 'public_date']);

            $table->foreign('gold_id')->references('id')->on('gold')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_prices');
    }
};
