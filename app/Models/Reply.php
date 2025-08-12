<?php

namespace App\Models;

use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reply extends Model
{
    use TranslationTrait;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'comment_id',
        'content',
        'likes'
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array'
        ];
    }

    public function setContentAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['content'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getContentAttribute($value)
    {
        $content = json_decode($value, true);
        $lang = app()->getLocale();
        return $content[$lang] ?? $content['en'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function userLikes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reply_likes');
    }
}
