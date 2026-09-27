<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappUser extends Model
{
    protected $fillable = [
        'phone',
        'name',
        'email',
        'preferred_lang',
        'session_state',
        'session_data',
    ];

    protected $casts = [
        'session_data' => 'array',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    public function chatHistory()
    {
        return $this->hasMany(ChatHistory::class, 'user_id');
    }

    public function credentials()
    {
        return $this->hasMany(Credential::class, 'user_id');
    }
}