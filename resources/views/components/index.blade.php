@props(['courses'])
@use('Carbon\Carbon')
@use('App\Enums\CourseStatusEnum')

<div class="active-courses">
    <div class="courses-grid">
        @forEach($courses as $course)
        <a href="{{route('courses.show_course', ['course' => $course->id])}}">
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
                    <div class="price">
                        @if($course->price == 0)
                            <span><i class="fas fa-dollar-sign"></i> FREE</span>
                        @else
                            <span><i class="fas fa-dollar-sign"></i>{{$course->price}}</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
