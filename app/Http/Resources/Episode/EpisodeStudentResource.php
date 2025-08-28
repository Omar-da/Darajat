<?php
//
//namespace App\Http\Resources\Episode;
//
//use App\Http\Resources\Quiz\StudentQuizResource;
//use Illuminate\Http\Request;
//use Illuminate\Http\Resources\Json\JsonResource;
//
//class EpisodeStudentResource extends JsonResource
//{
//    public function toArray(Request $request): array
//    {
//        $data = [
//            'id' => $this->id,
//            'title' => $this->title,
//            'episode_number' => $this->episode_number,
//            'duration' => $this->formatted_duration,
//            'views' => $this->views ?: 0,
//            'likes' => $this->likes ?: 0,
//            'is_watched' => (bool)$this->students()->whereUserId(auth('api')->id())->exists(),
//        ];
//
//        $data['quiz'] = $this->quiz ? new StudentQuizResource($this->quiz) : null;
//        return $data;
//    }
//}


namespace App\Http\Resources\Episode;

use App\Http\Resources\Quiz\StudentQuizResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'episode_number' => $this->episode_number,
            'duration' => $this->formatted_duration,
            'views' => $this->views ?: 0,
            'likes' => $this->likes ?: 0,
            'is_liked' => (bool)$this->userLikes()->whereUserId(auth('api')->id())->exists(),
            'has_quiz' => !is_null($this->quiz),
            'is_watched' => (bool)$this->students()->whereUserId(auth('api')->id())->exists(),
            'is_quiz_completed' => auth('api')->user()->episodes->contains($this->id) &&
                auth('api')->user()->episodes()->find($this->id)->pivot->pass_quiz,
        ];

        $data['quiz'] = $this->quiz ? new StudentQuizResource($this->quiz) : null;
        return $data;
    }
}
