@props(['deleted_courses'])
@use('Carbon\Carbon')
@use('App\Enums\CourseStatusEnum')

<div class="courses-container">
    <h1 class="pending-episodes-title">Deleted Courses <span class="pending-count-badge">{{ count($deleted_courses) }}</span></h1>
    
    <!-- Rejected Courses Section -->
    @if($deleted_courses->isNotEmpty())
    <div class="category-card">
        <x-index :courses="$deleted_courses"></x-index>
    </div>
    @else
        <div class="empty-state" style="padding: 6rem; font-size:25px">
            <i class="fas fa-question-circle"></i>
            <p>No deleted courses available</p>
        </div>
    @endif
</div>
    