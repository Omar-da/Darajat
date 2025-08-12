<?php

namespace App\Http\Resources\Badge;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
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
            'group' => $this->group,
            'description' => $this->description,
            'goal' => $this->goal,
            'image_url' => asset("build/assets/img/badges/$this->image_url")
        ];
    }
}
