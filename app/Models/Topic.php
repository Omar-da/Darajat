<?php

namespace App\Models;

use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use TranslationTrait;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'title' => 'array'
        ];
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getTitleAttribute($value)
    {
        $title = json_decode($value, true);
        $lang = app()->getLocale();
        return $title[$lang] ?? $title['en'];
    }

    public static function popular($query)
    {
        return $query->orderBy('title')->limit(5);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'completed_courses');
    }
}
