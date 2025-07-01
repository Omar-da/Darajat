<?php

namespace App\Http\Controllers\App;

use App\Models\Topic;
use App\Models\Course;
use App\Models\LanguageUser;
use App\Models\Payment;
use App\Responses\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CourseController extends Controller
{

    public function indexCourse($topicId){
        if (!Topic::find($topicId))
            return Response::error([], 'topic not found', 404);

         $courses = Course::where('topic_id',$topicId)->get();

        if($courses->isEmpty())
            return Response::error([], 'no courses in this topic', 404);

        return Response::success($courses, 'get courses successfully');
    }

    public function searchCourse($courseTitle){
        $courses= Course::where('title','LIKE',"%$courseTitle%")->get();
        if($courses->isEmpty())
            return Response::error([], 'no course have this title', 404);

        return Response::success($courses, 'get searsh courses successfully');

    }

    public function freeCourse(){
         $courses = Course::where('price', 0.0)->get();
        if($courses->isEmpty())
            return Response::error([], 'no course is free', 404);

        return Response::success($courses, 'get free course successfully');

    }

    public function paidCourse(){
        $courses = Course::where('price','>', 0.0)->get();
        if($courses->isEmpty())
            return Response::error([], 'no course is paid', 404);

        return Response::success($courses, 'get paid course successfully');

    }

    public function showAllCourses()
    {
        $courses = Course::get();
        if($courses->isEmpty())
            return Response::error([], 'no courses', 404);

        return Response::success($courses, 'get all courses successfully');
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
            $filePath = "assets/build/img/{$userName}/{$course->title}.pdf";
            
            if (!file_exists(public_path($filePath))) {
                $dirPath = public_path($filePath);
                if (!is_dir(dirname($dirPath)))
                    mkdir($dirPath, 0755, true);
                $download_url = $this->downloadFunction($course);
                $pdfContent = Http::get($download_url)->body();

                file_put_contents(public_path($filePath), $pdfContent);
            }

            // Display the PDF inline
            return response()->file(public_path($filePath), [
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
}

