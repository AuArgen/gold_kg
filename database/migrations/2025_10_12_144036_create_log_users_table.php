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
        Schema::create('log_users', function (Blueprint $table) {
            $table->id();
            // ID пользователя (null, если гость)
            $table->unsignedBigInteger('user_id')->nullable()->index();
            // IP адрес клиента
            $table->string('ip_address', 45);
            // Метод запроса (GET, POST, и т.д.)
            $table->string('method', 10);
            // Полный URL, к которому обратился пользователь
            $table->string('url', 512);
            // Роут, который был обработан (например, 'public.index')
            $table->string('route_name')->nullable();
            // HTTP статус ответа (200, 404, 500)
            $table->unsignedSmallInteger('status_code');
            // Время выполнения запроса в миллисекундах
            $table->decimal('response_time', 8, 3)->nullable();
            // Размер полезной нагрузки (body) запроса (если POST/PUT)
            $table->json('request_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_users');
    }
};
