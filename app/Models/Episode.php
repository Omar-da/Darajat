<?php

namespace App\Models;

use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Episode extends Model
{
    use SoftDeletes, TranslationTrait;

    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'title',
        'episode_number',
        'video_url',
        'duration',
        'image_url',
        'file_url',
        'views',
        'likes'
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array'
        ];
    }

    public function setTitleAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['title'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getTitleAttribute($value)
    {
        $title = json_decode($value, true);
        $lang = app()->getLocale();
        return $title[$lang] ?? $title['en'];
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration;
        return gmdate("i:s", $seconds);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function userLikes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'episode_likes');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'episode_user')->withPivot('pass_quiz');
    }
}
