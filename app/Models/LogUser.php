<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogUser extends Model
{
    use HasFactory;

    // Разрешаем массовое присвоение для всех полей, кроме защищенных
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Преобразование полей в типы данных
     * @var array
     */
    protected $casts = [
        'request_data' => 'array',
    ];

    /**
     * Связь с пользователем (если аутентифицирован)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
