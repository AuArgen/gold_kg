<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграций.
     * Создает таблицу 'contact_submissions' для хранения сообщений с формы обратной связи.
     */
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();

            // Имя пользователя, отправившего сообщение (максимум 255 символов)
            $table->string('name', 255);

            // Email пользователя (для обратной связи, индексируем для быстрого поиска)
            $table->string('email', 255)->index();

            // Текст сообщения
            $table->text('message');

            // Статус обработки: 'new' (новый), 'in_progress' (в работе), 'closed' (закрыт).
            $table->string('status')->default('new')->index();

            // Временные метки: created_at и updated_at
            $table->timestamps();
        });
    }

    /**
     * Откат миграций.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
