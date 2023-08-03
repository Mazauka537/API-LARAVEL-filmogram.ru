<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
        'follow_id' => 'integer',
    ];

    protected $fillable = [
        'user_id',
        'follow_id'
    ];
}
