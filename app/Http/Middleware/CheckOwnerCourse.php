<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Episode;
use App\Models\Quiz;
use App\Responses\Response;
use Closure;
use Illuminate\Http\Request;

class CheckOwnerCourse
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle($request, Closure $next)
    {
            // Get parameters from route
            $courseId = $request->route('course_id');
            $episodeId = $request->route('episode_id');
            $quizId = $request->route('quiz_id');

            // Determine which course to check based on available parameters
            if ($courseId) {
                $course = Course::findOrFail($courseId);
            } elseif ($episodeId) {
                $course = Episode::with('course')->findOrFail($episodeId)->course;
            } elseif ($quizId) {
                $course = Quiz::with('episode.course')->findOrFail($quizId)->episode->course;
            } else {
                return Response::error(__('msg.unauthorized'), 403);
            }

            // Check ownership
            if ($course->teacher_id != auth('api')->id()) {
                return Response::error(__('msg.unauthorized'), 403);
            }

            return $next($request);
    }
}
