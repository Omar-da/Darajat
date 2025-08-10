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
        $course = $request->route('course'); 
        
        $course = Course::findOrFail($course->id);
        $followedCourse = $user->followed_courses()
        ->where('course_id', $course->id)
        ->first();
                            
        if(!$course->has_certificate)
            return response()->json([
                'message' => 'Course has not certificate'
            ]);

        // Check if course exists in user's profile
        if (!$followedCourse) {
            return response()->json([
                'message' => 'Course not found in your profile'
            ], 403);
        }

        // Check course progress
        // if ($followedCourse->pivot->perc_progress != 100) {
        //     return response()->json([
        //         'message' => 'Course is not completed yet'
        //     ], 403);
        // }

        // Check quizzes completion
        // if ($followedCourse->num_of_quizzes != $followedCourse->num_of_completed_quizzes) {
        //     return response()->json([
        //         'message' => 'Quizzes are not completed yet'
        //     ], 403);
        // }

        return $next($request);
    }
}