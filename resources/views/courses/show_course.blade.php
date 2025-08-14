@use('Carbon\Carbon')
@use('App\Enums\CourseStatusEnum')

<x-layouts.header :title="$course->title" :with-footer="true">
    <div class="course-details-container">
        <div class="course-details-header">
            <div class="student-badge-container">
                <img class="course-details-image" src="{{ Storage::url('courses/' . $course->image_url) }}" alt="{{ $course->title }}">
            </div>

            <div class="course-details-info">
                <div class="course-details-title-container">
                    <h1 class="course-details-title">{{ $course->title }}</h1>
                    <div @class(['course-status-badge', 'active' => $course->status === CourseStatusEnum::APPROVED,
                                                        'rejected' => $course->status === CourseStatusEnum::REJECTED,
                                                        'pending' => $course->status === CourseStatusEnum::PENDING])>
                        @if($course->status === CourseStatusEnum::APPROVED)
                            <i class="fas fa-check-circle"></i> Active
                        @elseif($course->status === CourseStatusEnum::REJECTED)
                            <i class="fas fa-times-circle"></i> Rejected
                        @else
                            <i class="fas fa-clock"></i> Pending
                        @endif
                    </div>
                </div>

                <div class="course-details-meta">
                    <div class="course-meta-item">
                        <span class="course-meta-label">Teacher:</span>
                        <span class="course-meta-value">{{ $course->teacher->full_name }}</span>
                    </div>
                    <div class="course-meta-item">
                        <span class="course-meta-label">Language:</span>
                        <span class="course-meta-value">{{ $course->language->name }}</span>
                    </div>
                    <div class="course-meta-item" style="grid-column: 1 / -1;">
                        <span class="course-meta-label">Category:</span>
                        <span class="course-meta-value">{{ $course->topic->category->title }} / {{ $course->topic->title }}</span>
                    </div>
                    <div class="course-meta-item">
                        <span class="course-meta-label">Difficulty:</span>
                        <span class="course-meta-value">{{ $course->difficulty_level }}</span>
                    </div>
                    <div class="course-meta-item">
                        <span class="course-meta-label">Total Hours:</span>
                        <span class="course-meta-value">{{ $course->total_time }} h</span>
                    </div>
                    <div class="course-meta-item">
                        <span class="course-meta-label">Price:</span>
                        <span class="course-meta-value">
                            @if($course->price == 0)
                                FREE
                            @else
                                ${{ number_format($course->price, 2) }}
                            @endif
                        </span>
                    </div>
                    <div class="course-meta-item">
                        <span class="course-meta-label">Certificate:</span>
                        <span class="course-meta-value">
                            @if($course->has_certificate)
                                <i class="fas fa-certificate text-success"></i> Available
                            @else
                                <i class="fas fa-times-circle text-muted"></i> Not Available
                                @endif
                            </span>
                        </div>
                        <div class="course-meta-item">
                            <span class="course-meta-label">Quizzes:</span>
                            <span class="course-meta-value">{{ $course->episodes_count }}</span>
                        </div>
                        <div class="course-meta-item">
                            <span class="meta-label">Rate:</span>
                            <span>
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $course->rate)
                                        <i class="fas fa-star full-star"></i>
                                    @else
                                        <i class="far fa-star empty-star"></i>
                                    @endif
                                @endfor
                            </span>
                        </div>
                        <div class="course-meta-item">
                            @if($course->status === CourseStatusEnum::APPROVED)
                                <span class="course-meta-label">Approved At: </span>
                                {{ Carbon::parse($course->response_date)->format('M d, Y') }}
                            @elseif($course->status === CourseStatusEnum::REJECTED)
                                <span class="course-meta-label">Rejected At: </span> 
                                {{ Carbon::parse($course->response_date)->format('M d, Y')}}
                            @elseif($course->status === CourseStatusEnum::PENDING)
                                <span class="course-meta-label">Request Date: </span>    
                                {{ Carbon::parse($course->publishing_request_date)->format('M d, Y')}}
                            @endif
                        </div>
                        <div class="course-meta-item">
                            @if($course->status === CourseStatusEnum::APPROVED)
                                <span class="course-meta-label">Approved By: </span>   
                                {{ $course->admin->full_name}} 
                            @elseif($course->status === CourseStatusEnum::REJECTED)
                                <span class="course-meta-label">Rejected By: </span>  
                                {{ $course->admin->full_name}} 
                            @endif
                        </div>
                        @if($course->trashed())
                            <div class="course-meta-item">
                                <span class="course-meta-label"> Deleted At: </span>
                                {{ Carbon::parse($course->deleted_at)->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="course-details-description">
                <h2 class="course-section-title">Description</h2>
                <div class="course-description-content">
                    {{ $course->description }}
                </div>
            </div>
            @if($course->episodes->count() > 0)
                <div class="course-episodes-section">
                    <h2 class="course-section-title">Episodes ({{ $course->episodes->count() }})</h2>
                    
                    <div class="course-episodes-list">
                        @foreach($course->episodes as $episode)
                            <div class="course-episode-item">
                                <div class="episode-number">{{ $episode->episode_number }}</div>
                                <div class="episode-info">
                                    <h3 class="episode-title">
                                        {{ $episode->title }}
                                    </h3>
                                    <div class="episode-meta">
                                        <span class="episode-duration"><i class="fas fa-clock"></i> {{ $episode->duration }} min</span>
                                    </div>
                                </div>
                                <a href="{{ route('courses.show_episode', ['episode_id' => $episode->id]) }}" class="episode-view-btn">
                                    <i class="fas fa-play"></i> View
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div> 
        <div class="student-badge-with-icon">
            <i class="fas fa-users"></i>
            <span> {{ $course->num_of_students_enrolled }} Subscribers</span>
        </div> 
    </div>
</x-layouts.header>
