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
use Throwable;

class EpisodeController extends Controller
{
    private Episod  eService $episodeService;

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
        $episode = Episode::where('id', $episode_id)->firstOrFail();
        $course = Course::where('id', $episode->course_id)->firstOrFail();

        $videoPath = "courses/$course->id/episodes/$episode_id/video.mp4";

        if (!Storage::disk('local')->exists($videoPath)) {
            abort(404, 'Video file not found');
        }

        $video_url = Storage::disk('local')->temporaryUrl($videoPath, now()->addMinutes(30));

        return response()->json([
            'video_url' => $video_url
        ], headers: [
                'Content-Type' => 'video/mp4',
                'Content-Length' => Storage::disk('local')->size($videoPath),
                'Content-Disposition' => 'inline',  // Prevents "Save As" dialog
                'Cache-Control' => 'no-store',      // Disables browser caching
                'Accept-Ranges' => 'none'
            ]);
    }

    public function get_poster($episode_id)
    {
        $episode = Episode::where('id', $episode_id)->firstOrFail();
        $course = Course::where('id', $episode->course_id)->firstOrFail();
        $thumbnailPath = "courses/$course->id/episodes/$episode_id/thumbnail.jpg";

        $video_url = Storage::disk('local')->temporaryUrl($thumbnailPath, now()->addMinutes(30));

        return response()->json([
            'thumbnailPath' => $thumbnailPath
        ], headers: [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline',    // Prevents "Save As" dialog
                'Cache-Control' => 'no-store',        // No caching
                'X-Content-Type-Options' => 'nosniff' // Blocks MIME-type sniffing
            ]);
    }
}
