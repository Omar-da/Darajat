<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftCourse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'original_course_id',
        'topic_id',
        'teacher_id',
        'language_id',
        'title',
        'description',
        'image_url',
        'difficulty_level',
        'total_time',
        'price',
        'rate',
        'num_of_episodes',
        'num_of_students_enrolled',
        'publishing_request_date',
        'response_date',
        'status',
        'has_certificate',
    ];

    public function draft_episodes(): HasMany
    {
        return $this->hasMany(DraftEpisode::class);
    }

    public function original_course()
    {
        return $this->belongsTo(Course::class, 'original_course_id');
    }
}
