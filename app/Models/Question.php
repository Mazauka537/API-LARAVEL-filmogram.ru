<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer'
    ];

    protected $fillable = [
        'question',
        'email',
        'user_id'
    ];
}
