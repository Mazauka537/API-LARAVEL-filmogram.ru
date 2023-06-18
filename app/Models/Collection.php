<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'constant'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function films() {
        return $this->hasMany(Film::class);
    }

    public function savingUsers() {
        return $this->belongsToMany(User::class, 'saves', 'collection_id', 'user_id');
    }

    public function saves() {
        return $this->hasMany(Save::class);
    }
}
