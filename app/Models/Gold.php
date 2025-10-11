<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gold extends Model
{
    public function prices()
    {
        return $this->hasMany(GoldPrice::class, 'gold_id', 'id');
    }
}
