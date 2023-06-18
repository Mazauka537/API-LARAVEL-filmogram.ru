<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'auth_service'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['role'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'followers', 'follow_id', 'user_id');
    }

    public function subscriptions() {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follow_id');
    }

    public function collections() {
        return $this->hasMany(Collection::class);
    }

    public function saves() {
        return $this->belongsToMany(Collection::class, 'saves', 'user_id', 'collection_id');
    }

    public function restoreCodes() {
        return $this->hasMany(RestoreCode::class);
    }
}
