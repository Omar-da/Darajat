<?php

namespace App\Http\Middleware;

use App\Models\Coupon;
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
    public function handle(Request $request, Closure $next)
    {
        $course_id = $request->route()->hasParameter('course_id') ? $request->route()->parameter('course_id') : null;

        if (is_null($course_id)) {
            $episode_id = $request->route()->hasParameter('episode_id') ? $request->route()->parameter('episode_id') : null;
            if (is_null($episode_id)) {
                $quiz = Quiz::query()->find(request()->route()->parameter('quiz_id'));
                if (is_null($quiz)) {
                    return Response::error(__('msg.quiz_not_found'), 404);
                }
                $course = $quiz->episode->course;
            } else {
                $episode = Episode::query()->find($episode_id);
                if (is_null($episode)) {
                    return Response::error(__('msg.episode_not_found'), 404);
                }
                $course = $episode->course;
            }
        } else {
            $course = Course::query()->find($course_id);
            if (is_null($course)) {
                return Response::error(__('msg.course_not_found'), 404);
            }
        }

        if ($course->teacher_id != auth('api')->id()) {
            return Response::error(__('msg.unauthorized'), 403);
        }

        return $next($request);
    }
}
