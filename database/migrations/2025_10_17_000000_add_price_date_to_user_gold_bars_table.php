<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gold_bars', function (Blueprint $table) {
            // Добавляем столбец как nullable, чтобы избежать ошибки в SQLite
            $table->date('price_date')->after('purchase_price_per_bar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_gold_bars', function (Blueprint $table) {
            $table->dropColumn('price_date');
        });
    }
};
