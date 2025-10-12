<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    /**
     * Имя таблицы в базе данных.
     * @var string
     */
    protected $table = 'contact_submissions';

    /**
     * Поля, которые можно массово заполнять.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'message',
        'status', // По умолчанию 'new'
    ];
}
