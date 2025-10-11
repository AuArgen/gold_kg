<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoldPrice extends Model
{
    use HasFactory;

    // Определяем имя таблицы
    protected $table = 'gold_prices';

    /**
     * Поля, которые можно заполнять массивом (Mass Assignable).
     * ЭТО ИСПРАВЛЕНИЕ: включает все поля, которые вы передаете в GoldPrice::create().
     */
    protected $fillable = [
        'gold_id',
        'sale_kopecks',
        'buy_in_kopecks',
        'difference_sale_kopecks',
        'difference_buy_in_kopecks',
        'public_date',
    ];

    // Указываем, что public_date должно быть преобразовано в объект Carbon
    protected $casts = [
        'public_date' => 'date',
    ];

    /**
     * Отношение к слитку золота (многие к одному).
     */
    public function gold()
    {
        return $this->belongsTo(Gold::class, 'gold_id');
    }
}
