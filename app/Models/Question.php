<?php

namespace App\Models;

use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use TranslationTrait;
    public $timestamps = false;

    protected $fillable = [
        'question_number',
        'content',
        'answer_a',
        'answer_b',
        'answer_c',
        'answer_d',
        'explanation',
        'right_answer',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'explanation' => 'array',
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

    public function setExplanationAttribute($value): void
    {
        if(!is_null($value)) {
            $lang = $this->detectLanguage($value);
            $translatedContent = $this->translateContent($value, $lang);
            $this->attributes['explanation'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
        }
    }

    public function getExplanationAttribute($value)
    {
        $explanation = json_decode($value, true);
        $lang = app()->getLocale();
        return $explanation[$lang] ?? $explanation['en'] ?? null;
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
