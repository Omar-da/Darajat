<?php

namespace App\Http\Requests\User;

use App\Enums\EducationEnum;
use App\Enums\LevelEnum;
use App\Models\Speciality;
use App\Rules\SpecialityValue;
use App\Rules\ValidLevel;
use App\Traits\HandlesFailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileRequest extends FormRequest
{
    use HandlesFailedValidationTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50|regex:/^[\pL\s]+$/u',
            'last_name' => 'required|string|max:50|regex:/^[\pL\s]+$/u',
            'country_id' => 'required|exists:countries,id',
            'languages' => 'required|array',
            'languages.*.language_id' => 'required|exists:languages,id|distinct',
            'languages.*.level' => ['required', new ValidLevel()],
            'job_title_id' => 'nullable|exists:job_titles,id',
            'linked_in_url' => 'nullable|url',
            'education' => ['required', Rule::in(EducationEnum::values())],
            'university_id' => 'nullable|exists:universities,id',
            'speciality' => ['nullable', new SpecialityValue()],
            'work_experience' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*.skill_id' => 'exists:skills,id|distinct',
        ];
    }
}
