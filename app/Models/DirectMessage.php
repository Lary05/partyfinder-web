<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    use HasFactory;

    protected $table = 'direct_messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * The user who sent this message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * The user who received this message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
