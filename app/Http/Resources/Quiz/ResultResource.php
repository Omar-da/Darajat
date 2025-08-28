<?php

namespace App\Http\Resources\Quiz;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'mark' => $this->pivot->mark . '/' . $this->num_of_questions,
            'percentage_mark' => $this->pivot->percentage_mark . '%',
            'success' => (bool)$this->pivot->success
        ];
    }
}
