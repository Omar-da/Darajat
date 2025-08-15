<?php

namespace App\Livewire;

use App\Enums\CourseStatusEnum;
use App\Models\Category;
use App\Models\Course;
use App\Models\Topic;
use Livewire\Component;

class RejectedCourses extends Component
{
    public $rejectedCourses;

    public function repost(Course $course)
    {
        $course->status = CourseStatusEnum::APPROVED;
        $course->response_date = now()->format('Y-m-d H:i:s');
        $course->save();
    }

    public function render()
    {
        $this->rejectedCourses = Course::where('status', CourseStatusEnum::REJECTED
        )->with(['teacher' => function($q) {
            $q->withTrashed();
        }])->get();

        return view('livewire.rejected-courses')->layout('components.layouts.header', ['title' => 'Course Management', 'withFooter' => 'true']);
    }
}
