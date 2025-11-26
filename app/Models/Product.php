<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'url',
        'imageUrl',
        'brand',
        'name',
        'title',
        'currentPrice',
        'oldPrice',
        'discountPercentage',
        'isNew',
        'isGoodPrice',
        'actionPromotion',
        'rating',
        'reviewCount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'isNew' => 'boolean',
        'isGoodPrice' => 'boolean',
    ];
}
