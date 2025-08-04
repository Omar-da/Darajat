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
        
        if (auth()->user() ||
            auth('api')->user()->followed_courses->contains($episodeId) ||
            auth('api')->user()->published_courses->contains($episodeId) ||
            $episode->episode_number == 1) {
            return $next($request);
        }

        abort(403, 'Unauthorized access');
    }

}
