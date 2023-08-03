<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultCollection extends Model
{
    use HasFactory;

    protected $casts = [
        'genre' => 'integer',
        'year_from' => 'integer',
        'year_to' => 'integer',
        'rating_from' => 'integer',
        'rating_to' => 'integer',
    ];
}
