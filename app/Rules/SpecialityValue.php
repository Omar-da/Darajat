<?php

namespace App\Rules;

use App\Models\Speciality;
use App\Traits\TranslationTrait;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SpecialityValue implements ValidationRule
{
    use TranslationTrait;
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(is_numeric($value)) {
            if(is_null(Speciality::query()->find($value))) {
                $fail('Speciality id is not exist');
            }
        }
        $lang = $this->detectLanguage($value);
        if(Speciality::query()->where("name->{$lang}", $value)->exists()) {
            $fail('Speciality already exists');
        }
    }
}
