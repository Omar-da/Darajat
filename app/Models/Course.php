<?php

namespace App\Models;

use App\Enums\CourseStatusEnum;
use App\Enums\LevelEnum;
use App\Enums\RoleEnum;
use App\Traits\TranslationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use SoftDeletes, TranslationTrait;

    public $timestamps = false;

    protected $fillable = [
        'topic_id',
        'teacher_id',
        'language_id',
        'title',
        'description',
        'image_url',
        'difficulty_level',
        'total_time',
        'price',
        'rate',
        'num_of_episodes',
        'num_of_students_enrolled',
        'publishing_request_date',
        'response_date',
        'status',
        'has_certificate',
    ];

    protected function casts(): array
    {
        return [
            'difficulty_level' => LevelEnum::class,
            'status' => CourseStatusEnum::class,
            'title' => 'array',
            'description' => 'array',
            'has_certificate' => 'array',
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
        return $title[$lang] ?? $title['en'] ?? $value;
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
        return $description[$lang] ?? $description['en'] ?? $value;
    }

    public function setHasCertificateAttribute($value): void
    {
        if($value === true) {
            $value = ['en' => 'Yes', 'ar' => 'نعم'];
        }
        else {
            $value = ['en' => 'No', 'ar' => 'لا'];
        }
        $this->attributes['has_certificate'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getHasCertificateAttribute($value)
    {
        $has_certificate = json_decode($value, true);
        $lang = app()->getLocale();
        return $has_certificate[$lang] ?? $has_certificate['en'] ?? $value;
    }

    public static function popular($query)
    {
        return $query->orderBy('rate', 'desc')->limit(5);
    }

    public function calculatePercentageForValueRate($value): string
    {
        return round($this->students()->where('rate', $value)->count() / ($this->students()->count() != 0 ? $this->students()->count() : 1) * 100, 2) . '%';
    }

    public function hasSubscribers(): bool
    {
        return $this->num_of_students_enrolled > 0; 
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'student_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(language::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function draft_course(): HasOne
    {
        return $this->hasOne(DraftCourse::class);
    }

    protected static function boot()
    {
        parent::boot();

//        static::saving(function ($course) {
//            $user = $course->teacher;
//            if ($user->role != RoleEnum::TEACHER)
//                throw new \Exception('User is not teacher');
//
//            if(isEmpty($course->episodes))
//                throw new \Exception('Upload one episode at least');
//        });
    }
//     public function studentSubscribe($userId){
//        if($this->price ==0)
//            return true;
//        return $this->user()->where('student_id',$userId)->exists();
//    }

}
