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
    'vibes',
    'top_event_id',
    'spotify_anthem',
    'age',
    'birth_date',
    'latitude',
    'longitude',
    'discovery_distance',
    'discovery_min_age',
    'discovery_max_age',
    'gender',
    'discovery_gender'
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'birth_date'        => 'date',
            'vibes'             => 'array',   // JSON → PHP array automatically
            'spotify_anthem'    => 'array',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    public function favoriteEvents()
    {
        return $this->belongsToMany(Event::class, 'favorites');
    }

    /**
     * The user's pinned "top event" they plan to attend.
     */
    public function topEvent()
    {
        return $this->belongsTo(Event::class, 'top_event_id');
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
