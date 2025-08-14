<?php

namespace App\Models;

use App\Enums\DiscountTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'code',
        'discount_type',
        'discount_value',
        'expires_at',
        'max_uses',
        'use_count',
    ];

    protected $casts = [
        'discount_type' => DiscountTypeEnum::class,
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user', 'coupon_id', 'student_id');
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isFullyUsed(): bool
    {
        return $this->max_uses && $this->use_count == $this->max_uses ? true : false;
    }

    public function isValid() : bool
    {
        return !$this->isExpired() && !$this->isFullyUsed();
    }

    public static function isCodeUnique($value, $id = null) : bool
    {
        if(isset($id))
        {
            return !Coupon::query()->where('code', strtoupper($value))->whereNot('id', $id)->exists();
        }
        return !Coupon::query()->where('code', strtoupper($value))->exists();
    }
}
