<?php
namespace App\Http\Resources\Course\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Quiz\QuestionStudentResource;


class StudentQuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'num_of_questions' => $this->num_of_questions,
            'questions' => QuestionStudentResource::collection($this->questions)
        ];
    }
}
