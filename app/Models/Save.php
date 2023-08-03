<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Save extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
        'collection_id' => 'integer',
    ];

    protected $fillable = [
        'collection_id',
        'user_id'
    ];
}
