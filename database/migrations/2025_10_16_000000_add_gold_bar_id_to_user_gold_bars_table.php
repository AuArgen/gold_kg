<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gold_bars', function (Blueprint $table) {
            // Указываем правильное имя таблицы 'gold'
            $table->foreignId('gold_bar_id')->after('user_id')->constrained('gold')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_gold_bars', function (Blueprint $table) {
            $table->dropForeign(['gold_bar_id']);
            $table->dropColumn('gold_bar_id');
        });
    }
};
