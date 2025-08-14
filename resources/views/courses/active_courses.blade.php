<x-layouts.header title="Course Management" :with-footer="true">    
    <div class="courses-container">
    <h1 class="courses-title">Active Courses</h1>
        <!-- Active Courses Section -->
        <div class="category-card">
            <h2 class="category-title">{{$cate->title}}</h2>
            <h3 class="topic-title"><span class="arrow">-></span> {{$topic->title}}</h3>

            @if($courses->isNotEmpty())
                <x-index :courses="$courses"></x-index>
            @else
                <div class="empty-state" style="padding: 6rem; font-size:25px">
                    <i class="fas fa-question-circle"></i>
                    <p>No active courses available in this topic</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.header>
