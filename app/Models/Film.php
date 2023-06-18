<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'film_id',
        'order'
    ];

    public function collection() {
        return $this->belongsTo(Collection::class);
    }
}
