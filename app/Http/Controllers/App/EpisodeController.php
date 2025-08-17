<?php

namespace App\Http\Controllers\App;

use App\Http\Requests\Episode\CreateEpisodeRequest;
use App\Http\Requests\Episode\UpdateEpisodeRequest;
use App\Models\Course;
use App\Models\Episode;
use App\Responses\Response;
use App\Services\Episode\EpisodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Throwable;

class EpisodeController extends Controller
{
    private EpisodeService $episodeService;

    public function __construct(EpisodeService $episodeService)
    {
        $this->episodeService = $episodeService;
    }

    public function getToTeacher($course_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->getToTeacher($course_id);
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function getToStudent($course_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->getToStudent($course_id);
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function store(CreateEpisodeRequest $request, $course_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->store($request->validated(), $course_id);
            if ($data['code'] == 403 || $data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function update(UpdateEpisodeRequest $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->update($request->validated(), $id);
            if ($data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function showToTeacher($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->showToTeacher($id);
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function showToStudent($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->showToStudent($id);
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function destroy($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->destroy($id);
            if ($data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    // Add Like to specific episode.
    public function like($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->like($id);
            if ($data['code'] == 409 || $data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function finishEpisode($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->finishEpisode($id);
            if ($data['code'] == 409) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function downloadFile($id): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $data = [];
        try {
            $data = $this->episodeService->downloadFile($id);
            if($data instanceof StreamedResponse) {
                return $data;
            }

            if(isset($data['code']) && $data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }

            return $data;
        } catch (Throwable $th) {
            $message  = $th->getMessage();
            return Response::error($message);
        }
    }

    public function get_video($episode_id)
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
                'Content-Disposition' => 'inline; filename="protected_video.mp4"',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'self'",
                'Referrer-Policy' => 'no-referrer',
                'Permissions-Policy' => 'autoplay=()',
                'Cache-Control' => 'private, no-store, max-age=0, must-revalidate',
                'Accept-Ranges' => 'none', // Disable byte-range requests
                'X-Accel-Buffering' => 'no' // Disable buffering for some servers      // No caching
            ]
        );
    }

    public function get_poster($episode_id)
    {
        $episode = Episode::withTrashed()->where('id', $episode_id)->firstOrFail();
        $course = Course::where('id', $episode->course_id)->firstOrFail();
        $thumbnailPath = "courses/$course->id/episodes/$episode_id/thumbnail.jpg";


        return response()->file(
            Storage::disk('local')->path($thumbnailPath),
            [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline',    // Prevents "Save As" dialog
                'Cache-Control' => 'no-store',        // No caching
                'X-Content-Type-Options' => 'nosniff' // Blocks MIME-type sniffing
            ]
        );
    }
}
