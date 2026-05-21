<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'notification_enabled',
    'avatar_url',
    'bio',
    'age',
    'birth_date',
    'latitude',
    'longitude',
    'discovery_distance',
    'discovery_min_age',
    'discovery_max_age'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function favoriteEvents()
    {
        return $this->belongsToMany(Event::class, 'favorites');
    }

    public function reactions()
    {
        return $this->hasMany(EventReaction::class);
    }

    public function photos()
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function swipes()
    {
        return $this->hasMany(UserSwipe::class, 'swiper_id');
    }

    public function swipedBy()
    {
        return $this->hasMany(UserSwipe::class, 'swiped_id');
    }

    /**
     * Messages this user has sent.
     */
    public function sentMessages()
    {
        return $this->hasMany(DirectMessage::class, 'sender_id');
    }

    /**
     * Messages this user has received.
     */
    public function receivedMessages()
    {
        return $this->hasMany(DirectMessage::class, 'receiver_id');
    }

    public function getIsAdminAttribute()
{
    return $this->role === 'admin';
}
}
