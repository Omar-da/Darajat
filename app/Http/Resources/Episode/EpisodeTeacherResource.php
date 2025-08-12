<?php

namespace App\Http\Resources\Episode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Course\Teacher\TeacherQuizResource;
use App\Http\Resources\Course\Student\StudentQuizResource;
use Illuminate\Support\Facades\Storage;

class EpisodeTeacherResource extends JsonResource
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
            'is_watched' => (bool)$this->students()->whereUserId(auth('api')->id())->exists(),
        ];

        $data['quiz'] = $this->quiz ? new TeacherQuizResource($this->quiz) : null;
        return $data;
    }
}
