<?php

namespace App\Rules;

use App\Enums\LevelEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLevel implements ValidationRule
{

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     * @param $type
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, LevelEnum::values()) && !in_array($value, LevelEnum::getTranslatedValues())) {
            if (!request()->route()->hasParameter('courses')) {
                $values = array_filter(LevelEnum::getTranslatedValues(), function ($item) {
                    return $item != LevelEnum::MOTHER_TONGUE->label();
                });
                $fail(__('msg.course_difficulty_level') . implode(', ', $values));
            } else {
                $fail(__('msg.language_level') . LevelEnum::getTranslatedValuesString());
            }
        }
    }
}
