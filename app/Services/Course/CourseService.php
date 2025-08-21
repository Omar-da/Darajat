<?php

namespace App\Services\Course;

use App\Enums\CourseStatusEnum;
use App\Enums\LevelEnum;
use App\Http\Resources\Course\Student\CourseForStudentResource;
use App\Http\Resources\Course\Student\CourseWithDetailsForStudentResource;
use App\Http\Resources\Course\Student\EnrolledCourseForStudentResource;
use App\Http\Resources\Course\Teacher\CourseForTeacherResource;
use App\Http\Resources\Course\Teacher\CourseWithArrangementForTeacherResource;
use App\Http\Resources\Course\Teacher\CourseWithDetailsForTeacherResource;
use App\Http\Resources\Course\Teacher\DeletedCourseForTeacherResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\Language;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    // Get all courses in the platform, with a maximum of 5 courses per page.
    public function index(): array
    {
        $courses = Course::query()
            ->where('status', CourseStatusEnum::APPROVED)
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->paginate(5);

        return [
            'data' => CourseForStudentResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'has_more_pages' => $courses->hasMorePages(),
                'next_page' => $courses->hasMorePages() ? $courses->currentPage() + 1 : null,
            ],
            'message' => __('msg.courses_retrieved'),
            'code' => 200
        ];
    }

    // Load more courses, they are not appearing on the last page.
    public function loadMore($request): array
    {
        if ($request['type'] == 'all')
        {
            $courses = Course::query()
                ->where('status', CourseStatusEnum::APPROVED)
                ->orderBy('rate', 'desc')
                ->orderBy('num_of_students_enrolled', 'desc')
                ->paginate(5, '*', 'page', $request['page']);
        }
        else if ($request['type'] == 'free')
        {
            $courses = Course::query()
                ->where([
                    'status' => CourseStatusEnum::APPROVED,
                    'price' => '0'
                ])
                ->orderBy('rate', 'desc')
                ->orderBy('num_of_students_enrolled', 'desc')
                ->paginate(5, '*', 'page', $request['page']);
        }
        else if ($request['type'] == 'paid') {
            $courses = Course::query()
                ->where('status', CourseStatusEnum::APPROVED)
                ->where('price', '>', 0)
                ->orderBy('rate', 'desc')
                ->orderBy('num_of_students_enrolled', 'desc')
                ->paginate(5, '*', 'page', $request['page']);
        }

        return [
            'data' => CourseForStudentResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'has_more_pages' => $courses->hasMorePages(),
                'next_page' => $courses->hasMorePages() ? $courses->currentPage() + 1 : null,
            ],
            'message' => __('msg.courses_retrieved'),
            'code' => 200
        ];
    }

    public function getCoursesForCategory($category_id): array
    {
        $category = Category::query()->find($category_id);
        if (!$category)
            return ['message' => __('msg.category_not_found'), 'code' => 404];

        $courses = Course::query()->whereHas('topic', function ($query) use ($category_id) {
            $query->where('category_id', $category_id);
        })
            ->where('status', CourseStatusEnum::APPROVED)
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->get();

        return ['data' => CourseForStudentResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getCoursesForTopic($topic_id): array
    {
        if (!Topic::query()->find($topic_id))
            return ['message' => __('msg.topic_not_found'), 'code' => 404];

        $courses = Course::query()
            ->where([
                'status' => CourseStatusEnum::APPROVED,
                'topic_id' => $topic_id
            ])
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->get();
        return ['data' => CourseForStudentResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getCoursesForLanguage($language_id): array
    {
        if (!Language::query()->find($language_id))
            return ['message' => __('msg.language_not_found'), 'code' => 404];

        $courses = Course::query()
            ->where([
                'status' => CourseStatusEnum::APPROVED,
                'language_id' => $language_id
            ])
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->get();
        return ['data' => CourseForStudentResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function search($title): array
    {
        $courses = Course::query()
            ->where('status', CourseStatusEnum::APPROVED)
            ->where('title', 'LIKE', "%$title%")
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->get();
        if ($courses->isEmpty()) {
            return [
                'data' => [],
                'message' => __('msg.no_courses_found') . $title,
                'suggestions' => Course::popular(Course::query())->pluck('title'),
                'code' => 200
            ];
        }
        return ['data' => CourseForStudentResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getFreeCourses(): array
    {
        $courses = Course::query()
            ->where([
                'status' => CourseStatusEnum::APPROVED,
                'price' => 0
            ])
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->paginate(5);

        return [
            'data' => CourseForStudentResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'has_more_pages' => $courses->hasMorePages(),
                'next_page' => $courses->hasMorePages() ? $courses->currentPage() + 1 : null,
            ],
            'message' => __('msg.free_courses'),
            'code' => 200
        ];
    }

    public function getPaidCourses(): array
    {
        $courses = Course::query()
            ->where('status', CourseStatusEnum::APPROVED)
            ->where('price', '>', 0)
            ->orderBy('rate', 'desc')
            ->orderBy('num_of_students_enrolled', 'desc')
            ->paginate(5);

        return [
            'data' => CourseForStudentResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'has_more_pages' => $courses->hasMorePages(),
                'next_page' => $courses->hasMorePages() ? $courses->currentPage() + 1 : null,
            ],
            'message' => __('msg.paid_courses'),
            'code' => 200
        ];
    }

    public function showToTeacher($id): array
    {
        $course = Course::query()->find($id);

        return ['data' => new CourseWithDetailsForTeacherResource($course), 'message' => __('msg.course_retrieved'), 'code' => 200];
    }

    public function showToStudent($id): array
    {
        $course = Course::query()
            ->where('status', CourseStatusEnum::APPROVED)
            ->find($id);
        if (is_null($course))
            return ['message' => __('msg.course_not_found'), 'code' => 404];

        return ['data' => new CourseWithDetailsForStudentResource($course), 'message' => __('msg.course_retrieved'), 'code' => 200];
    }

    public function getDraftCoursesToTeacher(): array
    {
        $courses = Course::query()
            ->where([
                'teacher_id' => auth('api')->id(),
                'status' => CourseStatusEnum::DRAFT,
            ])
            ->latest('created_at')
            ->get();

        return ['data' => CourseForTeacherResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getPendingCoursesToTeacher(): array
    {
        $courses = Course::query()
            ->where([
                'teacher_id' => auth('api')->id(),
                'status' => CourseStatusEnum::PENDING,
            ])
            ->latest('publishing_request_date')
            ->get();

        return ['data' => CourseForTeacherResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getApprovedCoursesToTeacher(): array
    {
        $courses = Course::query()
            ->where([
                'teacher_id' => auth('api')->id(),
                'status' => CourseStatusEnum::APPROVED,
            ])
            ->withCount('students')
            ->orderBy('rate', 'desc')
            ->orderBy('students_count', 'desc')
            ->get();

        return ['data' => CourseForTeacherResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getRejectedCoursesToTeacher(): array
    {
        $courses = Course::query()
            ->where([
                'teacher_id' => auth('api')->id(),
                'status' => CourseStatusEnum::REJECTED,
            ])
            ->latest('publishing_request_date')
            ->get();

        return ['data' => CourseForTeacherResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function createDraftCourse($request): array
    {
        $request['teacher_id'] = auth('api')->id();
        $request['image_url'] = basename($request['image_url']->store('courses', 'uploads'));

        if(!in_array($request['difficulty_level'], LevelEnum::values())) {
            foreach (LevelEnum::values() as $value) {
                if($request['difficulty_level'] == LevelEnum::from($value)->label()) {
                    $request['difficulty_level'] = $value;
                    break;
                }
            }
        }
        $course = Course::query()->create($request);
        $course->refresh();
        return ['data' => new CourseWithDetailsForTeacherResource($course), 'message' => __('msg.courses_created'), 'code' => 201];
    }

    public function updateApprovedCourse($request, $id): array
    {
        $course = Course::query()->find($id);

        if ($course->status !== CourseStatusEnum::APPROVED)
            return ['message' => __('msg.can_not_updated_course') . $course->status->label() . __('msg.status'), 'code' => 403];

        $course->price = $request['price'];
        $course->save();

        return ['data' => new CourseForTeacherResource($course), 'message' => __('msg.course_updated'), 'code' => 200];
    }

    public function updateDraftCourse($request, $id): array
    {
        $course = Course::findOrFail($id);

        if ($course->status !== CourseStatusEnum::DRAFT)
            return ['message' => __('msg.can_not_updated_course') . $course->status->label() . __('msg.status'), 'code' => 403];

        Storage::disk('uploads')->delete("courses/$course->image_url");
        $request['image_url'] = basename($request['image_url']->store('courses', 'uploads'));
        if(!in_array($request['difficulty_level'], LevelEnum::values())) {
            foreach (LevelEnum::values() as $value) {
                if($request['difficulty_level'] == LevelEnum::from($value)->label()) {
                    $request['difficulty_level'] = $value;
                    break;
                }
            }
        }

        $course->update($request);

        return ['data' => new CourseForTeacherResource($course), 'message' => __('msg.course_updated'), 'code' => 200];
    }

    public function destroyDraftCourse($course_id): array
    {
        $course = Course::findOrFail($course_id);
        if ($course->status !== CourseStatusEnum::DRAFT)
            return ['message' => __('msg.can_not_delete_course') . $course->status->label() . __('msg.status'), 'code' => 403];

        Storage::disk('uploads')->delete("courses/$course->image_url");
        $course->forceDelete();

        return ['data' => new DeletedCourseForTeacherResource($course), 'message' => __('msg.course_deleted'), 'code' => 200];
    }

    public function getDeletedCoursesToTeacher(): array
    {
        $courses = Course::onlyTrashed()->where('teacher_id', auth('api')->id())->get();

        return ['data' => DeletedCourseForTeacherResource::collection($courses), 'message' => __('msg.course_retrieved'), 'code' => 200];
    }

    public function submitCourse($id): array
    {
        $course = Course::query()->find($id);

        if ($course->num_of_episodes == 0) {
            return ['message' => __('msg.add_one_episode'), 'code' => 422];
        }

        if ($course->status === CourseStatusEnum::PENDING)
            return ['message' => __('msg.course_already_submitted'), 'code' => 422];
        else if ($course->status === CourseStatusEnum::APPROVED)
            return ['message' => __('msg.course_already_published'), 'code' => 422];
        else if ($course->status === CourseStatusEnum::REJECTED)
            return ['message' => __('msg.course_rejected'), 'code' => 409];


        $course->update([
            'status' => CourseStatusEnum::PENDING,
            'publishing_request_date' => now()->format('Y-m-d H:i:s'),
            'response_date' => null
        ]);

        return ['message' => __('msg.course_submitted'), 'code' => 200];
    }

    public function evaluation($request, $id): array
    {
        $user = auth('api')->user();
        $course = Course::query()->where('status', CourseStatusEnum::APPROVED)->find($id);

        if($user->id == $course->teacher_id)
            return ['message' => __('msg.unauthorized'), 'code' => 403];

        //        $course->students()->updateExistingPivot($student_id, ['rate' => $request['rate']]);
        $course->students()->sync(
            [
                $user->id => ['rate' => $request['rate']]
            ],
            false
        );
        $course->rate = round($course->students()->pluck('rate')->avg(), 2);
        $course->save();

        return ['data' => new CourseWithDetailsForStudentResource($course), 'message' => __('msg.course_evaluated'), 'code' => 200];
    }

    public function getCoursesForTopicForTeacherWithArrangement($topic_id): array
    {
        $topic = Topic::query()->find($topic_id);
        if (is_null($topic)) {
            return ['message' => __('msg.topic_not_found'), 'code' => 404];
        }

        $courses = $topic->courses()->where('status', CourseStatusEnum::APPROVED)
            ->orderBy('num_of_students_enrolled', 'desc')
            ->get();
        return ['data' => CourseWithArrangementForTeacherResource::collection($courses), 'message' => __('msg.courses_retrieved'), 'code' => 200];
    }

    public function getFollowedCoursesForStudent(): array
    {
        $user = auth('api')->user();

        $followed_courses = $user->followed_courses;

        return ['data' => EnrolledCourseForStudentResource::collection($followed_courses), 'message' => __('msg.courses_retrieved')];
    }

}
