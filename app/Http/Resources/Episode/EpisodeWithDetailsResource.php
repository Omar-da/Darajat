<?php

namespace App\Http\Resources\Episode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EpisodeWithDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $directory = "courses/{$this->course_id}/episodes/{$this->id}";
        $file = collect(Storage::disk('local')->files($directory))
            ->first(fn($f) => str_contains(basename($f), 'file'));

        return [
            'id' => $this->id,
            'title' => $this->title,
            'episode_number' => $this->episode_number,
            'duration' => $this->formatted_duration,
            'views' => $this->views ? $this->views : 0,
            'likes' => $this->likes ? $this->likes : 0,
            'is_watched' => (bool)$this->students()->whereUserId(auth()->id())->exists(),
            'is_liked' => (bool)$this->userLikes()->whereUserId(auth()->id())->exists(),
            'has_quiz' => !is_null($this->quiz),
            'is_quiz_completed' => auth('api')->user()->episodes->contains($this->id) && (bool)auth('api')->user()->episodes()->find($this->id)->pivot->pass_quiz,
            'has_file' => !is_null($file),
        ];
    }
}
