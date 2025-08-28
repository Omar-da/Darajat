<?php

namespace App\Http\Resources\Reply;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReplyResource extends JsonResource
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
            'comment_id' => $this->comment_id,
            'content' => $this->content,
            'reply_date' => Carbon::parse($this->reply_date)->diffForHumans(),
            'is_liked' => (bool)auth('api')->user()->likeReply()->where('reply_id', $this->id)->exists(),
            'is_my_reply' => $this->user->id == auth('api')->id(),
            'likes' => $this->likes ? $this->likes : 0,
            'replier' => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'profile_image_url' => $this->user->profile_image_url ? Storage::url("profiles/{$this->user->profile_image_url}") : null,
            ]
        ];
    }
}
