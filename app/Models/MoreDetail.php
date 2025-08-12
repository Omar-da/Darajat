<?php

namespace App\Models;

use App\Enums\EducationEnum;
use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MoreDetail extends Model
{
    use TranslationTrait;

    protected $fillable = [
        'user_id',
        'job_title_id',
        'country_id',
        'linked_in_url',
        'education',
        'university',
        'speciality',
        'work_experience',
        'is_banned',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'education' => EducationEnum::class,
            'university' => 'array',
            'speciality' => 'array',
            'work_experience' => 'array',
        ];
    }

    public function setUniversityAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['university'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getUniversityAttribute($value)
    {
        $university = json_decode($value, true);
        $lang = app()->getLocale();
        return $university[$lang] ?? $university['en'] ?? null;
    }

    public function setSpecialityAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['speciality'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getSpecialityAttribute($value)
    {
        $speciality = json_decode($value, true);
        $lang = app()->getLocale();
        return $speciality[$lang] ?? $speciality['en'] ?? null;
    }

    public function setWorkExperienceAttribute($value): void
    {
        $lang = $this->detectLanguage($value);
        $translatedContent = $this->translateContent($value, $lang);
        $this->attributes['work_experience'] = json_encode($translatedContent, JSON_UNESCAPED_UNICODE);
    }

    public function getWorkExperienceAttribute($value)
    {
        $work_experience = json_decode($value, true);
        $lang = app()->getLocale();
        return $work_experience[$lang] ?? $work_experience['en'] ?? null;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'language_user')->withPivot('level');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_user', 'more_detail_id', 'skill_id');
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }
}
