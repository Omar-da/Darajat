<?php

namespace App\Http\Resources\Course\Teacher;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseWithDetailsForTeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Course)
            $what_will_you_learn = $this->episodes->pluck('title');
        else
            $what_will_you_learn = $this->draft_episodes->pluck('title');

        return [
            'id' => $this->id,
            'image_url' => Storage::url("courses/$this->image_url"),
            'title' => $this->title,
            'category' => [
                'id' => $this->topic->category->id,
                'title' => $this->topic->category->title,
                'image_url' => asset("img/categories/{$this->topic->category->image_url}")
            ],
            'topic' => [
                'id' => $this->topic->id,
                'title' => $this->topic->title
            ],
            'description' => $this->description,
            'language' => [
                'id' => $this->language->id,
                'name' => $this->language->name
            ],
            'what_will_you_learn' => $what_will_you_learn,
            'teacher' => [
                'id' => $this->teacher->id,
                'full_name' => $this->teacher->full_name,
                'profile_image_url' => $this->teacher->profile_image_url ? Storage::url("profiles/{$this->teacher->profile_image_url}") : null
            ],
            'difficulty_level' => $this->difficulty_level->label(),
            'num_of_hours' => $this->total_time ? floor($this->total_time / 3600) : 0,
            'price' => $this->price . '$',
            'rate' => [
                'course_rating' => $this->rate ? $this->rate : 0,
                '5' => $this->rate ? $this->calculatePercentageForValueRate(5) : '0%',
                '4' => $this->rate ? $this->calculatePercentageForValueRate(4) : '0%',
                '3' => $this->rate ? $this->calculatePercentageForValueRate(3) : '0%',
                '2' => $this->rate ? $this->calculatePercentageForValueRate(2) : '0%',
                '1' => $this->rate ? $this->calculatePercentageForValueRate(1) : '0%',
            ],
            'num_of_episodes' => $this->num_of_episodes ? $this->num_of_episodes : 0,
            'status' => $this->status->label(),
            'response_date' => $this->response_date,
            'publishing_request_date' => $this->publishing_request_date,
            'has_certificate' => (bool)$this->has_certificate,
            'total_quizzes' => $this->total_quizzes ? $this->total_quizzes : 0,
            'num_of_students_enrolled' => $this->num_of_students_enrolled ? $this->num_of_students_enrolled : 0,
            'created_at' => $this->created_at,
        ];
    }
}
