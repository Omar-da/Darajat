<?php

namespace App\Models;

use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speciality extends Model
{
    use TranslationTrait;

    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array'
        ];
    }

    public function setNameAttribute($value): void
    {
        if (is_array($value) && array_key_exists('ar', $value)) {
            $this->attributes['name'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $content = is_array($value) ? reset($value) : $value;
        $lang = $this->detectLanguage($content);
        $translatedContent = $this->translateContent($content, $lang);

        $this->attributes['name'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getNameAttribute($value)
    {
        $name = json_decode($value, true);
        $lang = app()->getLocale();
        return $name[$lang] ?? $name['en'] ?? $value;
    }

    public function moreDetails(): HasMany
    {
        return $this->hasMany(MoreDetail::class);
    }
}
