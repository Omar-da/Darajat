<?php

namespace App\Livewire;

use App\Enums\CourseStatusEnum;
use App\Models\Course;
use Livewire\Component;

class PendingCourses extends Component
{
    public $pendingCourses;

    public function approve(Course $course)
    {
        $course->status = CourseStatusEnum::APPROVED;
        $course->admin_id = auth()->user()->id;
        $course->response_date = now()->format('Y-m-d H:i:s');
        $course->save();
    }
    
    public function reject(Course $course)
    {
        $course->status = CourseStatusEnum::REJECTED;
        $course->admin_id = auth()->user()->id;
        $course->response_date = now()->format('Y-m-d H:i:s');
        $course->save();
    }

    public function render()
    {
        $this->pendingCourses = Course::where('status', CourseStatusEnum::PENDING)
        ->with(['teacher' => function($q) {
                $q->withTrashed();
        }])->get();
        
        return view('livewire.pending-courses');
    }
}
