<?php

namespace App\Http\Resources\Course\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DeletedCourseForTeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array|mixed[]
     */
    public function toArray(Request $request)
    {
        return array_merge(
            (new CourseForTeacherResource($this))->toArray($request),
            ['deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s')]
        );
    }
}
