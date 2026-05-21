<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSwipe extends Model
{
    use HasFactory;

    protected $table = 'user_swipes';

    protected $fillable = ['swiper_id', 'swiped_id', 'is_right_swipe'];

    public function swiper()
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    public function swiped()
    {
        return $this->belongsTo(User::class, 'swiped_id');
    }
}
