<?php

namespace App\Http\Resources\Course\Student;

use App\Enums\CourseStatusEnum;
use App\Http\Resources\Episode\EpisodeWithDetailsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseWithDetailsForStudentResource extends JsonResource
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
            'image_url' => Storage::url("courses/$this->image_url"),
            'title' => $this->title,
            'category' => $this->topic->category->title,
            'topic' => $this->topic->title,
            'description' => $this->description,
            'language' => $this->language->name,
            'what_will_you_learn' => $this->episodes->pluck('title'),
            'teacher' => [
                'id' => $this->teacher->id,
                'full_name' => $this->teacher->full_name,
                'profile_image_url' => $this->teacher->profile_image_url ? Storage::url($this->teacher->profile_image_url) : null
            ],
            'is_my_course' => (bool)auth('api')->id() == $this->teacher->id,
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
            'num_of_episodes' => $this->num_of_episodes ? $this->num_of_episodes  : 0,
            'publishing_date' => $this->response_date,
            'has_certificate' =>  $this->has_certificate,
            'total_quizzes' =>  $this->total_quizzes ? $this->total_quizzes : 0,
            'num_of_students_enrolled' => $this->num_of_students_enrolled ? $this->num_of_students_enrolled : 0,
            'first_episode' => new EpisodeWithDetailsResource($this->episodes()->where('episode_number', 1)->first()),
        ];
    }
}
