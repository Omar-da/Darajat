<?php
// app/Http/Middleware/EnsureCertificateEligibility.php

namespace App\Http\Middleware;

use App\Models\Course;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CertificateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        // Extract course ID from route (assuming route is like /certificates/{course})
        $courseId = $request->route('course_id');

        $course = Course::findOrFail($courseId);
        $followedCourse = $user->followed_courses()
                            ->where('course_id', $courseId)
                            ->firstOrFail();

        if(!$course->has_certificate)
            return response()->json([
                'message' => 'Course has not certificate'
            ]);

        // Check if course exists in user's profile
        if (!$followedCourse)
            return response()->json([
                'message' => 'Course not found in your profile'
            ], 403);

        if ($followedCourse->pivot->get_certificate)
            return response()->json([
                'message' => 'You have already obtained certificate'
            ], 403);

        // Check course progress
        if($followedCourse->pivot->is_episodes_completed) 
            return response()->json([
                'message' => 'Course is not completed yet'
            ], 403);

        // Check quizzes completion
        if($followedCourse->pivot->is_quizzes_completed) 
            return response()->json([
                'message' => 'Quizzes is not completed yet'
            ], 403);

        return $next($request);
    }
}
