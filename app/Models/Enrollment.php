<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'internship_id',
        'payment_id',
        'status',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(WhatsappUser::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function credential()
    {
        return $this->hasOne(Credential::class);
    }
}