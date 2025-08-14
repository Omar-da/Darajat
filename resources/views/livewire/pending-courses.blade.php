@props(['pendingCourses'])

@use('Carbon\Carbon')

<div class="tab-content">
    <h1 class="pending-episodes-title">Pending Courses <span class="pending-count-badge">{{ count($pendingCourses) }}</span></h1>
    
    @if($pendingCourses->isNotEmpty())
        <div class="pending-episodes-list">
            @foreach($pendingCourses as $course)
                <div class="pending-episode-card">
                    <div class="pending-episode-media">
                        <img src="{{Storage::url("courses/$course->image_url")}}" alt="Course Image" class="pending-episode-thumbnail">
                    </div>
                    <div class="pending-episode-content">
                        <h3 class="course-title">{{$course->title}}</h3>
                        <div class="course-meta">
                            <span class="course-meta-label" style="font-weight: 200">Category : </span><span class="course-meta-value">Mathematics and Sciences / Science</span>
                        </div>
                        <div>
                            <span class="course-teacher">By <span class="teacher-name">{{$course->teacher->full_name}}</span></span>
                        </div>
                        <div class="course-meta">
                            <span class="meta-item"><i class="fas fa-clock"></i> {{$course->total_time}}h</span>
                            <span class="meta-item"><i class="fas fa-video"></i> {{$course->num_of_episodes}} Episodes</span>
                            <span class="meta-item"><i class="fas fa-signal"></i> {{$course->difficulty_level}}</span>
                        </div>
                        <div>
                            <span class="course-date-line">
                                <i class="fas fa-calendar-alt"></i> 
                                Request Date: <span class="course-date">{{ Carbon::parse($course->publishing_request_date)->format('M d, Y')}}</span>
                                <span class="price" style="margin-left: 40px">
                                    @if($course->price == 0)
                                    <span><i class="fas fa-dollar-sign"></i> FREE</span>
                                    @else
                                    <span><i class="fas fa-dollar-sign"></i>{{$course->price}}</span>
                                    @endif
                                </span>
                            </span>    
                        </div>
                        <div class="pending-episode-actions">
                            <a href="{{ route('courses.show_course', ['course' => $course->id]) }}" class="pending-action-btn pending-details-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <button wire:click="approve({{$course->id}})" type="button" class="pending-action-btn pending-approve-btn">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button wire:click="reject({{$course->id}})" type="button" class="pending-action-btn pending-reject-btn">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state" style="padding: 6rem; font-size:25px">
            <i class="fas fa-question-circle"></i>
            <p>No pending courses available</p>
        </div>
    @endif
</div>
