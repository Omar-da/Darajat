<?php

namespace App\Http\Controllers\App;

use App\Http\Requests\Course\CourseRequest;
use App\Http\Requests\Course\EvaluationCourseRequest;
use App\Http\Requests\Course\LoadMoreCoursesRequest;
use App\Http\Requests\Course\UpdateDraftCourse;
use App\Responses\Response;
use App\Services\Course\CourseService;
use Illuminate\Http\JsonResponse;
use App\Models\Course;
use GuzzleHttp\Client;
use Throwable;

class CourseController extends Controller
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->index();
            return Response::successForPaginate($data['data'], $data['meta'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    // Load 5 courses for specific page, they are not appearing on the last page.
    public function loadMore(LoadMoreCoursesRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->loadMore($request->validated());
            return Response::successForPaginate($data['data'], $data['meta'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getCoursesForCategory($category_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getCoursesForCategory($category_id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getCoursesForTopic($topic_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getCoursesForTopic($topic_id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getCoursesForLanguage($language_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getCoursesForLanguage($language_id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function search($title): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->search($title);
            if (array_key_exists('suggestions', $data)) {
                return Response::successForSuggestions($data['data'], $data['message'], $data['suggestions'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getFreeCourses(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getFreeCourses();
            return Response::successForPaginate($data['data'], $data['meta'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getPaidCourses(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getPaidCourses();
            return Response::successForPaginate($data['data'], $data['meta'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getDraftCoursesToTeacher(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getDraftCoursesToTeacher();
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getPendingCoursesToTeacher(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getPendingCoursesToTeacher();
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getApprovedCoursesToTeacher(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getApprovedCoursesToTeacher();
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getRejectedCoursesToTeacher(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getRejectedCoursesToTeacher();
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function showToTeacher($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->showToTeacher($id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function showToStudent($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->showToStudent($id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function createDraftCourse(CourseRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->createDraftCourse($request->validated());
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function updateDraftCourse(UpdateDraftCourse $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->updateDraftCourse($request->validated(), $id);
            if ($data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function updateApprovedCourse(CourseRequest $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->updateApprovedCourse($request->validated(), $id);
            if ($data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function destroyDraftCourse($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->destroyDraftCourse($id);
            if ($data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getDeletedCoursesToTeacher(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getDeletedCoursesToTeacher();
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }


    public function submitCourse($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->submitCourse($id);
            if ($data['code'] == 409 || $data['code'] == 422) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function evaluation(EvaluationCourseRequest $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->evaluation($request->validated(), $id);
            if ($data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getCoursesForTopicForTeacherWithArrangement($topic_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getCoursesForTopicForTeacherWithArrangement($topic_id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getFollowedCoursesForStudent(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->getFollowedCoursesForStudent();
            return Response::success($data['data'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function enrollInFreeCourse($id)
    {
        $data = [];
        try {
            $data = $this->courseService->enrollInFreeCourse($id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getCertificate($course_id)
    {
        $course = Course::findOrFail($course_id);
        $user = auth('api')->user();
        $apiKey = env('CERTIFIER_API_KEY');
        $followed_course = $user->followed_courses()->where('course_id', $course->id)->firstOrFail();

        if ($followed_course->pivot->get_certificate)
            return response()->json([
                'message' => 'You have already obtained certificate'
            ]);


        $client = new Client();

        $response = $client->request('POST', 'https://api.certifier.io/v1/credentials/create-issue-send',
            [
                'headers' => [
                    'Certifier-Version' => '2022-10-26',
                    'accept' => 'application/json',
                    'authorization' => "Bearer $apiKey",
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'groupId' => '01jsxgwpm02gd1j9pq679rx3de',
                    'recipient' => [
                        'name' => $user->full_name,
                        'email' => 'omaraldalati3@gmail.com',
                    ],
                    'certificate' => [
                        'issued_on' => now()->toDateString(),
                    ],
                    'customAttributes' => [
                        'custom.course_classification' => $course->topic->category->title,
                        'custom.course_name' => $course->title,
                        'custom.degree' => $course->teacher->full_name,
                    ]
                ]
            ]);

        $body = $response->getBody()->getContents();
        $credentialData = json_decode($body, true);

        return response()->json([
            'success' => true,
            'credential_url' => "https://credsverse.com/credentials/{$credentialData['publicId']}"
        ]);
    }

}

