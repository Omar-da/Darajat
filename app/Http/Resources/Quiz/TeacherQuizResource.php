<?php
namespace App\Http\Resources\Quiz;

use App\Http\Resources\Question\QuestionTeacherResource;
use App\Models\DraftQuiz;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TeacherQuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if($this->resource instanceof Quiz)
            $questions = $this->questions;
        else if($this->resource instanceof DraftQuiz)
            $questions = $this->draft_questions;
        return [
            'id' => $this->id,
            'num_of_questions' => $this->num_of_questions,
            'questions' => QuestionTeacherResource::collection($questions)
        ];
    }
}
