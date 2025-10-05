<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');

            // Права доступа (по умолчанию false)
            $table->boolean('GET')->default(false);
            $table->boolean('POST')->default(false);
            $table->boolean('PUT')->default(false);
            $table->boolean('DELETE')->default(false);
            $table->boolean('PATCH')->default(false);

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // чтобы не было дублирующихся записей user+role
            $table->unique(['user_id', 'role_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
