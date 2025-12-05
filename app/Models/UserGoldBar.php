<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGoldBar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gold_bar_id',
        'purchase_date',
        'quantity',
        'purchase_price_per_bar',
        'price_date', // Добавлено
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'purchase_date' => 'date',
        'price_date' => 'date', // Добавлено
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goldBar()
    {
        return $this->belongsTo(Gold::class, 'gold_bar_id');
    }
}
