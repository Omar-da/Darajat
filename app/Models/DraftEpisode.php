<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftEpisode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'draft_course_id',
        'title',
        'episode_number',
        'duration',
        'views',
        'likes',
    ];

    public function draft_course(): BelongsTo
    {
        return $this->belongsTo(DraftCourse::class);
    }

    public function draft_quizzes(): HasMany
    {
        return $this->hasMany(DraftQuiz::class);
    }
}