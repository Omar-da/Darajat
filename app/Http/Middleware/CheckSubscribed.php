<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Responses\Response;
use Closure;
use Illuminate\Http\Request;

class CheckSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $course_id = $request->route('course_id');
        $course = Course::find($course_id);
        if (is_null($course)) {
            return Response::error(__('msg.course_not_found'), 404);
        }

        if (!auth('api')->user()->followed_courses->contains($course_id) &&
            !auth('api')->user()->published_courses->contains($course_id)) {
            return Response::error(__('msg.are_not_subscribed'), 403);
        }

        return $next($request);
    }
}
