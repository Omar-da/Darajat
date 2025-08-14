@props(['activeTab', 'categories', 'deleted_courses'])

<div class="courses-container">
        <h1 class="courses-title">Course Management</h1>
        
        <div class="courses-tabs">
            
            <!-- Tab Labels -->
            <div class="tab-labels">
                <label for="active-tab" @class(['tab', 'selected' => $activeTab === 'active_courses']) wire:click="changeTab('active_courses')">Active Courses</label>
                <label for="rejected-tab" @class(['tab', 'selected' => $activeTab === 'rejected_courses'])wire:click="changeTab('rejected_courses')">Rejected Courses</label>
                <label for="deleted-tab" @class(['tab', 'selected' => $activeTab === 'deleted_courses']) wire:click="changeTab('deleted_courses')">Deleted Courses</label>
                <label for="pending-tab" @class(['tab', 'selected' => $activeTab === 'pending_courses']) wire:click="changeTab('pending_courses')">Pending Courses</label>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content-wrapper">
                <!-- Active Courses -->
                @if($activeTab === 'active_courses')
                    @include('courses.active-courses-categories', ['categories' => $categories])
                <!-- Rejected Courses -->
                @elseif($activeTab === 'rejected_courses')
                    @livewire('rejected-courses')
                <!-- Pending Courses -->
                @elseif($activeTab === 'pending_courses')
                    @livewire('pending-courses')
                <!-- Deleted Courses -->
                @elseif($activeTab === 'deleted_courses')
                    @include('courses.deleted-courses', ['deleted_courses' => $deleted_courses])
                @endif
            </div>
        </div>
    </div>  