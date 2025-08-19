<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatusEnum;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SoftDeleteController
{
    
    public function destroyAfterPublishing(Course $course): array
    {
        if ($course->status !== CourseStatusEnum::APPROVED || $course->num_of_students_enrolled > 0)
            return ['message' => __('msg.can_not_delete_course'), 'code' => 403];
        
        $course->delete();

        return ['message' => __('msg.course_deleted'), 'code' => 200];
    }

    public function restore(Course $course): array
    {
        $course->update([
            'status' => CourseStatusEnum::PENDING,
            'publishing_request_date' => now()->format('Y-m-d H:i:s')
        ]);
        unset($course['response_date']);
        $course->restore();

        return ['message' => __('msg.course_restored'), 'code' => 200];
    }
}
