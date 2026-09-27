<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'translations',
        'description_translations',
        'duration',
        'mode',
        'fee',
        'portal_course_id',
        'portal_price',
        'is_active',
    ];

    protected $casts = [
        'translations'             => 'array',
        'description_translations' => 'array',
        'is_active'                => 'boolean',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}