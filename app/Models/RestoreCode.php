<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestoreCode extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $fillable = [
        'code',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
