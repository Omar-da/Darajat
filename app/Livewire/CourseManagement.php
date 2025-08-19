<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Course;
use Livewire\Component;

class CourseManagement extends Component
{
    public $activeTab = 'active_courses', $categories;

    protected $queryString = [
        'activeTab' => ['except' => 'active_courses']
    ];

    public function mount()
    {
        $this->categories = Category::with('topics')->get();
    }

    public function render()
    {
        $deleted_courses = Course::onlyTrashed()->with(['teacher' => function($q) {
            $q->withTrashed();
        }])->get();

        return view('livewire.course-management', compact(['deleted_courses']))->layout('components.layouts.header', ['title' => 'Course Management']);
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }
}
