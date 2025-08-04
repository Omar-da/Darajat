<?php

namespace App\Http\Controllers\App;

use App\Models\Topic;
use App\Models\Course;
use App\Models\LanguageUser;
use App\Models\Payment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use App\Http\Requests\LoadMore\LoadMoreRequest;
use App\Http\Requests\Course\CourseRequest;
use App\Http\Requests\Course\EvaluationCourseRequest;
use App\Http\Requests\Course\LoadMoreCoursesRequest;
use App\Responses\Response;
use App\Services\Course\CourseService;
use Illuminate\Http\JsonResponse;
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
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
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
            if($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function paymentProcess(Course $course)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $teacher = $course->teacher;
        $platformFee = $course->price * 0.10; // 10% fee

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $course->price * 100, // in cents
                'currency' => 'usd',
                'application_fee_amount' => $platformFee * 100, // 10% platform fee
                'transfer_data' => [
                    'destination' => $teacher->stripe_connect_id,
                ],
            ]);

            // Save payment record
            Payment::create([
                'course_id' => $course->id,
                'student_id' => auth('api')->user()->id,
                'teacher_id' => $teacher->id,
                'amount' => $course->price,
                'platform_fee' => $platformFee,
                'currency' => 'usd',
                'stripe_payment_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
        ]);

            return redirect()->route('student.dashboard')->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

       public function getCertificate(Course $course)
        {
            $user = auth('api')->user();
            $userName = $user->first_name . '_' . $user->last_name; 
            $filePath = "private/certificates/{$userName}/{$course->title}.pdf";

            if (!Storage::disk('local')->exists($filePath)) {
                $download_url = $this->downloadFunction($course);
                $pdfContent = Http::get($download_url)->throw()->body();

                Storage::disk('local')->put($filePath, $pdfContent);
            }

            return Storage::disk('local')->response($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="certificate.pdf"'
            ]);
        }

        public function downloadCertificate(Course $course)
        {
            // Always re-download fresh copy for download route
            $download_url = $this->downloadFunction($course);

            // Force download
            return redirect()->away($download_url);
        }

        private function downloadFunction(Course $course)
        {
            $user = auth('api')->user();
    
            // Fetch from Certifier API (replace with your actual API call)
            $apiKey = env('CERTIFIER_API_KEY'); 
            $baseUrl = 'https://api.certifier.io/v1/credentials';

            $client = new Client();

            $response = $client->post("{$baseUrl}/issue", [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'recipient' => [
                        'name' => "$user->first_name $user->last_name", 
                    ],
                    'template_id' => '01jsm7nt1nc9kwc5g1c9tn1ryk',
                    'issued_on' => now()->toDateString(),
                    'metadata' => [
                        'degree' => "$course->teacher->first_name $course->teacher->last_name",
                        'course_name' => $course->title,
                        'course_classification' => $course->topic->category
                    ],
                ],
            ]);
            
            $credential = json_decode($response->getBody(), true);
            return $credential['download_url']; // e.g., "https://certifier.io/cred/CRED_123"
        }
    public function showToStudent($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->showToStudent($id);
            if($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function store(CourseRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->store($request->validated());
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function updateDraftCourse(CourseRequest $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->updateDraftCourse($request->validated(), $id);
            if($data['code'] == 404 || $data['code'] == 403) {
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
            if($data['code'] == 404 || $data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function destroy($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->destroy($id);
            if($data['code'] == 404 || $data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function publishCourse($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->publishCourse($id);
            if($data['code'] == 404 || $data['code'] == 422) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function evaluation(EvaluationCourseRequest $request , $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->courseService->evaluation($request->validated(), $id);
            if($data['code'] == 422) {
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
            if($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }
}

