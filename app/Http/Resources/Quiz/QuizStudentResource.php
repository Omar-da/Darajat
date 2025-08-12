<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\Question\QuestionStudentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizStudentResource extends JsonResource
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
            'num_of_questions' => $this->num_of_questions,
            'questions' => QuestionStudentResource::collection($this->questions)
        ];
    }
}
