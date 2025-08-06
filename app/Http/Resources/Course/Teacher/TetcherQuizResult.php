<?php
namespace App\Http\Resources\Course\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Quiz\QuestionTeacherResource;


class TeacherQuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'num_of_questions' => $this->num_of_questions,
            'questions' => QuestionTeacherResource::collection($this->questions)
        ];
    }
}
