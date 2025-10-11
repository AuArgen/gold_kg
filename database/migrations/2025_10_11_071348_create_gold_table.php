<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Импортируем фасад DB

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Создание таблицы 'gold'
        Schema::create('gold', function (Blueprint $table) {
            $table->id();
            // Поле 'name' будет содержать вес в граммах (для отображения)
            $table->string('name')->unique();
            // Поле 'weight_units' будет хранить вес, умноженный на 10000
            // для точности в 4 знака после запятой. Используем unsignedInteger.
            $table->unsignedInteger('weight_units')->unique();
            $table->timestamps();
        });

        // 2. Данные для вставки
        // Вес в граммах умножаем на 10000, чтобы сохранить 4 знака после запятой.
        $weights = [
            // 1.0000 * 10000 = 10000
            ['name' => '1.00g', 'weight_units' => 10000],
            // 2.0000 * 10000 = 20000
            ['name' => '2.00g', 'weight_units' => 20000],
            // 5.0000 * 10000 = 50000
            ['name' => '5.00g', 'weight_units' => 50000],
            // 10.0000 * 10000 = 100000
            ['name' => '10.00g', 'weight_units' => 100000],
            // 31.1035 * 10000 = 311035 (Это обеспечивает 4 знака точности)
            ['name' => '31.1035g (Oz)', 'weight_units' => 311035],
            // 100.0000 * 10000 = 1000000
            ['name' => '100.00g', 'weight_units' => 1000000],
        ];

        // 3. Вставка данных
        DB::table('gold')->insert($weights);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold');
    }
};
