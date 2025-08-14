<?php

namespace App\Http\Resources\Course\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EnrolledCourseForStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' =>  Storage::url($this->image_url),
            'title' => $this->title,
            'teacher' => $this->teacher->full_name,
            'price' => $this->price . '$',
            'rate' => $this->rate,
            'language' => $this->language->name,
            'num_of_students_enrolled' => $this->num_of_students_enrolled,
            'percentage_progress' => auth('api')->user()->followed_courses()->where('course_id', $this->id)->first()->pivot->perc_progress . '%',
        ];
    }
}
