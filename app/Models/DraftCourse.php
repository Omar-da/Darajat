<?php

namespace App\Models;

use App\Enums\CourseStatusEnum;
use App\Enums\LevelEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftCourse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
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
        'was_edited'
    ];

        protected function casts(): array
        {
            return [
                'difficulty_level' => LevelEnum::class,
                'status' => CourseStatusEnum::class,
                'title' => 'array',
                'description' => 'array',
                'has_certificate' => 'array',
            ];
        }


    public function draft_episodes(): HasMany
    {
        return $this->hasMany(DraftEpisode::class);
    }

    public function original_course()
    {
        return $this->belongsTo(Course::class, 'original_course_id');
    }

        public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(language::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }   
}
