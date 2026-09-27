<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'portal_username',
        'portal_password_hash',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(WhatsappUser::class, 'user_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}