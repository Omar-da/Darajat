<?php
// app/Http/Middleware/EnsureCertificateEligibility.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CertificateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        
        // Extract course ID from route (assuming route is like /certificates/{course})
        $courseId = $request->route('course'); 

        $followedCourse = $user->moreDetail->followed_courses()
                            ->where('course_id', $courseId)
                            ->first();

        // Check if course exists in user's profile
        if (!$followedCourse) {
            return response()->json([
                'message' => 'Course not found in your profile'
            ], 403);
        }

        // Check course progress
        if ($followedCourse->pivot->perc_progress != 100) {
            return response()->json([
                'message' => 'Course is not completed yet'
            ], 403);
        }

        // Check quizzes completion
        if ($followedCourse->num_of_quizzes != $followedCourse->num_of_completed_quizzes) {
            return response()->json([
                'message' => 'Quizzes are not completed yet'
            ], 403);
        }

        return $next($request);
    }
}