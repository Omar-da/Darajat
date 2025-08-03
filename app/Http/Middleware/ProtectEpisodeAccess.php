<?php

namespace App\Http\Middleware;

use App\Models\Episode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectEpisodeAccess
{
    public function handle($request, Closure $next)
    {
        $episodeId = $request->route('episode_id'); // From route parameter
        $episode = Episode::withTrashed()->where('id', $episodeId)->firstOrFail();
        
        if ($request->is('dashboard/*') && auth()->user() || $request->is('api/*') && auth()->user()->followed_courses()->where('course_id', $episode->course_id)->exists()) {
            return $next($request);
        }

        abort(403, 'Unauthorized access');
    }

}
