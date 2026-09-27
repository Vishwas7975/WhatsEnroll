<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    protected $table = 'chat_history';

    protected $fillable = [
        'user_id',
        'direction',
        'message_type',
        'body',
        'media_url',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(WhatsappUser::class, 'user_id');
    }
}