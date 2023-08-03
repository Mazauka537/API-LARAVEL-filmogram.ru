<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationToken extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $fillable = [
        'token',
        'user_id'
    ];
}
