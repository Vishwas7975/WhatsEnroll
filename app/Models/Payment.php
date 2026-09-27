<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'internship_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'amount',
        'status',
        'screenshot_path',
        'txn_id',
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

    public function enrollment()
    {
        return $this->hasOne(Enrollment::class);
    }
}