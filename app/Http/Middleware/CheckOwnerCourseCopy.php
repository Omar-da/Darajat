<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\DraftCourse;
use App\Models\DraftEpisode;
use App\Models\DraftQuiz;
use App\Responses\Response;
use Closure;

class CheckOwnerCourseCopy
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle($request, Closure $next)
    {
            // Get parameters from route
            $original_courseId = $request->route('course_id');
            $courseId = $request->route('draft_course_id');
            $episodeId = $request->route('draft_episode_id');
            $quizId = $request->route('draft_quiz_id');
            
            // Determine which course to check based on available parameters
            
            if ($original_courseId) {
                $course = Course::findOrFail($original_courseId);
            } elseif ($courseId) {
                $course = DraftCourse::with('original_course')->findOrFail($courseId)->original_course;
            } elseif ($episodeId) {
                $course = DraftEpisode::with('course.original_course')->findOrFail($episodeId)->draft_course->original_course;
            } elseif ($quizId) {
                $course = DraftQuiz::with('episode.course.original_course')->findOrFail($quizId)->draft_episode->draft_course->original_course;
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
