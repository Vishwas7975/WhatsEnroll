<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotification extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'payload',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(WhatsappUser::class, 'user_id');
    }
}