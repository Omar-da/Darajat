<?php

namespace App\Models;

use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use TranslationTrait;
    public $timestamps = false;

    protected $fillable = [
        'group',
        'level',
        'description',
        'goal',
        'image_url',
        'admin_id'
    ];

    protected function casts(): array
    {
        return [
            'group' => 'array',
            'description' => 'array'
        ];
    }

    public function setGroupAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['group'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getGroupAttribute($value)
    {
        $group = json_decode($value, true);
        $lang = app()->getLocale();
        return $group[$lang] ?? $group['en'];
    }

    public function setDescriptionAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['description'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getDescriptionAttribute($value)
    {
        $description = json_decode($value, true);
        $lang = app()->getLocale();
        return $description[$lang] ?? $description['en'];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
