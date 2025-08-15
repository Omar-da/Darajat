@props(['rejectedCourses'])
@use('Carbon\Carbon')
@use('App\Enums\CourseStatusEnum')

<div class="courses-container">
    <h1 class="pending-episodes-title">Rejected Courses <span class="pending-count-badge">{{ count($rejectedCourses) }}</span></h1>
    
    <!-- Rejected Courses Section -->
    @if($rejectedCourses->isNotEmpty())
        <div class="category-card">
            <div class="active-courses">
                <div class="courses-grid">
                    @forEach($rejectedCourses as $course)
                        <div class="course-card">
                            <div class="course-image">
                                <img src="{{Storage::url("courses/$course->image_url")}}" alt="Course Image">
                            </div>
                            <div class="course-details">
                                <h3 class="course-title">{{$course->title}}</h3>
                                <span class="course-teacher">By  <span class="teacher-name">{{$course->teacher->full_name}}</span></span>
                                <div class="course-meta">
                                    <span class="meta-item"><i class="fas fa-clock"></i> {{$course->total_time}}h</span>
                                    <span class="meta-item"><i class="fas fa-video"></i> {{$course->num_of_episodes}} Episodes</span>
                                    <span class="meta-item"><i class="fas fa-signal"></i> {{$course->difficulty_level}}</span>
                                    <div class="course-date-line">
                                        <i class="fas fa-calendar-alt"></i> 
                                        @if($course->trashed())
                                            Deleted At: <span class="course-date">{{ Carbon::parse($course->deleted_at)->format('M d, Y') }}</span>
                                        @elseif($course->status === CourseStatusEnum::APPROVED)
                                            Approving Info: <span class="course-date">{{ $course->admin->full_name . ' / ' . Carbon::parse($course->response_date)->format('M d, Y') }} </span>
                                        @elseif($course->status === CourseStatusEnum::REJECTED)
                                            Rejecting Info: <span class="course-date"> {{ $course->admin->full_name . ' / ' . Carbon::parse($course->response_date)->format('M d, Y')}} </span>
                                        @elseif($course->status === CourseStatusEnum::PENDING)
                                            Request Date: <span class="course-date">{{ Carbon::parse($course->publishing_request_date)->format('M d, Y')}} </span>
                                        @endif
                                    </div>    
                                </div>
                                <div style="display: flex; justify-content:space-between; align-items: center">
                                    <div class="price">
                                        @if($course->price == 0)
                                            <span><i class="fas fa-dollar-sign"></i> FREE</span>
                                        @else
                                            <span><i class="fas fa-dollar-sign"></i>{{$course->price}}</span>
                                        @endif
                                    </div>
                                    <div style="display: flex; justify-content:space-between">
                                        <a href="{{ route('courses.show_course', ['course' => $course->id]) }}" class="edit-button" style="font-size: 20px; padding: 11px">Show Details</a>
                                        <span wire:click="repost({{$course->id}})" class="edit-button" style="font-size: 20px; padding: 11px">Repost</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="empty-state" style="padding: 6rem; font-size:25px">
            <i class="fas fa-question-circle"></i>
            <p>No rejected courses available</p>
        </div>
    @endif
</div>
    