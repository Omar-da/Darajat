<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
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

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

}
