<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'name' => 'array'
        ];
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getNameAttribute($value)
    {
        $name = json_decode($value, true);
        $lang = app()->getLocale();
        return $name[$lang] ?? $name['en'];
    }

    public function moreDetails(): BelongsToMany
    {
        return $this->belongsToMany(MoreDetail::class, 'language_user');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'language_id');
    }
}
