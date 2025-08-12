<?php

namespace App\Http\Middleware;

use App\Models\Episode;
use App\Responses\Response;
use Closure;
use Illuminate\Http\Request;

class ProtectEpisodeAccess
{
    public function handle($request, Closure $next)
    {
        $episodeId = $request->route('episode_id'); // From route parameter
        $episode = Episode::query()->find($episodeId);

        if(is_null($episode)) {
            return Response::error('Episode not found!', 404);
        }

        if (auth()->user() ||
            auth('api')->user()->followed_courses->contains($episode->course_id) ||
            $episode->episode_number == 1) {
            return $next($request);
        }

        abort(403, 'Unauthorized access!');
    }

}
