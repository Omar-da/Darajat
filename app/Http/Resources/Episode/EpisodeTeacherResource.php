<?php

namespace App\Http\Resources\Episode;

use App\Http\Resources\Quiz\TeacherQuizResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EpisodeTeacherResource extends JsonResource
{


    public function toArray(Request $request): array
    {
        $directory = "courses/{$this->course_id}/episodes/{$this->id}";
        $file = collect(Storage::disk('local')->files($directory))
            ->first(fn($f) => str_contains(basename($f), 'file'));

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'episode_number' => $this->episode_number,
            'duration' => $this->formatted_duration,
            'views' => $this->views ?: 0,
            'likes' => $this->likes ?: 0,
            'has_file' => !is_null($file),
        ];

        $data['quiz'] = $this->quiz ? new TeacherQuizResource($this->quiz) : null;
        return $data;
    }
}
