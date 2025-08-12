<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
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

    public function moreDetails(): BelongsToMany
    {
        return $this->belongsToMany(MoreDetail::class, 'skill_user', 'skill_id', 'more_detail_id');
    }
}
