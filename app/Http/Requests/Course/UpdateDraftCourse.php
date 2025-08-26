<?php

namespace App\Http\Requests\Course;

use App\Rules\ValidLevel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDraftCourse extends FormRequest
{
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
            'topic_id' => 'required|exists:topics,id',
            'language_id' => 'required|exists:languages,id',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,bmp,jpg,gif,svg|max:2048',
            'difficulty_level' => ['required', new ValidLevel('course')],
            'price' => 'required|numeric|min:0',
            'has_certificate' => ['required', 'boolean'],
        ];
    }
}
