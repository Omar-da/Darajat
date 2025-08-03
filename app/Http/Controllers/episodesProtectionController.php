<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class episodesProtectionController
{
    public function video_protection($episode_id)
    {
        $episode = Episode::withTrashed()->where('id', $episode_id)->firstOrFail();
        $course = Course::where('id', $episode->course_id)->firstOrFail();

        $videoPath = "courses/$course->id/episodes/$episode_id/video.mp4";
        
        if (!Storage::disk('local')->exists($videoPath)) {
            abort(404, 'Video file not found');
        }

        return Storage::disk('local')->response(
            $videoPath,
            'episode-video.mp4',
            [
                'Content-Type' => 'video/mp4',
                'Content-Length' => Storage::disk('local')->size($videoPath),
                'Content-Disposition' => 'inline',  // Prevents "Save As" dialog
                'Cache-Control' => 'no-store', // Disables browser caching
                'Accept-Ranges' => 'none'
            ]
        );
    }

    public function image_protection($episode_id)
    {
        $episode = Episode::withTrashed()->where('id', $episode_id)->firstOrFail();
        $course = Course::where('id', $episode->course_id)->firstOrFail();
        $thumbnailPath = "courses/$course->id/episodes/$episode_id/thumbnail.jpg";
        
        
        return response()->file(
            Storage::disk('local')->path($thumbnailPath),
            [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline', // Prevents "Save As" dialog
                'Cache-Control' => 'no-store', // No caching
                'X-Content-Type-Options' => 'nosniff' // Blocks MIME-type sniffing
            ]
        );
    }
}
