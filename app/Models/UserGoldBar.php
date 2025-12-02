<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGoldBar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_date',
        'quantity',
        'purchase_price_per_bar',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
