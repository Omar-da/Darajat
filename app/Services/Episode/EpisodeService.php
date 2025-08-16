<?php

namespace App\Services\Episode;

use App\Enums\CourseStatusEnum;
use App\Http\Resources\Episode\EpisodeStudentResource;
use App\Http\Resources\Episode\EpisodeTeacherResource;
use App\Http\Resources\Episode\EpisodeWithDetailsResource;
use App\Models\Course;
use App\Models\Episode;
use App\Traits\BadgeTrait;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EpisodeService
{
    use BadgeTrait;

    public function getToTeacher($course_id): array
    {
        $course = Course::query()
            ->with(['episodes.quiz.questions'])// Eager load relationships
            ->find($course_id);

        $episodes = $course->episodes;
        return ['data' => EpisodeTeacherResource::collection($episodes), 'message' => __('msg.episodes_retrieved'), 'code' => 200];
    }

    public function getToStudent($course_id): array
    {
        $user = auth('api')->user();
        $course = $user->followed_courses()
            ->where('course_id', $course_id)
            ->with(['episodes.quiz.questions']) // Eager load relationships
            ->first();

        $episodes['episodes'] = EpisodeStudentResource::collection($course->episodes);
        $episodes['progress_percentage'] = $course->pivot->perc_progress . '%';
        $episodes['num_of_completed_quizzes'] = $course->pivot->num_of_completed_quizzes;
        $episodes['get_certificate'] = (bool)$course->pivot->get_certificate;
        return ['data' => $episodes, 'message' => __('msg.episodes_retrieved'), 'code' => 200];
    }

    public function store($request, $course_id): array
    {
        $course = Course::query()
            ->where([
                'teacher_id' => auth('api')->id(),
                'id' => $course_id
            ])->first();
        if (is_null($course)) {
            return ['message' => __('msg.course_not_found'), 'code' => 404];
        }

        if (!($course->status === CourseStatusEnum::DRAFT || $course->status === CourseStatusEnum::REJECTED)) {
            return ['message' => __('msg.can_not_add_episode') . $course->status->label() . '!', 'code' => 403];
        }

        $request['course_id'] = $course_id;
        $episode = Episode::query()->create($request);
        $episode_path = "courses/$course->id/episodes/$episode->id";

        $request['image_url']->storeAs($episode_path, 'thumbnail.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video.mp4', 'local');

        if(request()->hasFile('file_url')) {
            $request['file_url']->storeAs($episode_path, 'file.' . $request['file_url']->getClientOriginalExtension(), 'local');
        }

        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video.mp4")->getDurationInSeconds();

        $episode->update([
            'duration' => $request['duration']
        ]);

        $course->update([
            'num_of_episodes' => $course->num_of_episodes + 1,
            'total_time' => $course['total_time'] + $request['duration'],
        ]);
        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_created'), 'code' => 201];
    }

    public function update($request, $id): array
    {
        $episode = Episode::query()->find($id);

        $course = $episode->course;
        if (!($episode->course->status === CourseStatusEnum::DRAFT || $episode->course->stauts === CourseStatusEnum::REJECTED)) {
            return ['message' => __('msg.can_not_update_episode') . $episode->course->status->label() . '!', 'code' => 403];
        }

        $episode_path = "courses/$course->id/episodes/$episode->id";
        $request['image_url']->storeAs($episode_path, 'thumbnail.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video.mp4', 'local');

        if(request()->hasFile('file_url')) {
            $request['file_url']->storeAs($episode_path, 'file.' . $request['file_url']->getClientOriginalExtension(), 'local');
        } else {
            $file = collect(Storage::disk('local')->files($episode_path))
                ->first(fn($f) => str_contains(basename($f), 'file'));
            if(!is_null($file)) {
                Storage::disk('local')->delete("$episode_path/file." . pathinfo($file, PATHINFO_EXTENSION));
            }
        }

        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video.mp4")->getDurationInSeconds();
        $episode->update($request);
        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_updated'), 'code' => 200];
    }

    public function showToTeacher($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_retrieved'), 'code' => 200];
    }

    public function showToStudent($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_retrieved'), 'code' => 200];
    }

    public function destroy($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        $course = $episode->course;
        if ($course->status === CourseStatusEnum::APPROVED) {
            return ['message' => __('msg.can_not_delete_episode') . $course->status->label() . '!', 'code' => 403];
        }

        $min_episode = Episode::withTrashed()->where('course_id', $course->id)->orderBy('episode_number')->first();
        $episode->update([
            'episode_number' => $min_episode->episode_number - 1,
        ]);

//        $episode->quiz()->delete();
        $episode->forceDelete();
        foreach ($course->episodes as $episode) {
            if ($episode->id != $id) {
                $episode->update([
                    'episode_number' => $episode->episode_number - 1
                ]);
            }
        }

        return ['message' => __('msg.episode_deleted'), 'code' => 200];
    }

    // Finish an episode when the student has watched it completely.
    public function finishEpisode($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        if($episode->course->teacher_id == auth('api')->id())
            return ['message' => __('msg.teacher_watched_his_course'), 'code' => 409];

        if ($user->episodes()->where('episode_id', $id)->exists())
            return ['message' => __('msg.episode_watched_before'), 'code' => 409];

        // Episode has been watched
        $user->episodes()->attach($episode);
        $episode->increment('views');
        $episode->course->teacher->statistics()->where('title->en', 'Acquired Views')->first()->pivot->increment('progress');

        // Update progress in course
        $course = $user->followed_courses()->wherePivot('course_id', $episode->course->id)->first();
        $course->pivot->increment('progress');
        $course->pivot->update(['perc_progress' => ($course->pivot->progress * 100) / $episode->course->episodes->count()]);

        // Update activity of user
        $user->moreDetail->is_active_today = true;
        $user->moreDetail->save();

        // Check to user end the course
        if ($course->pivot->perc_progress == 100) {
            $course->pivot->update(['episodes_completed' => true]);
            if ($course->pivot->quizzes_completed) {
                $this->checkStatistic($course);
            }
        }

        return ['message' => __('msg.is_active'), 'code' => 200];
    }

    // Add Like to specific episode.
    public function like($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        if($episode->course->teacher_id == auth('api')->id())
            return ['message' => __('msg.teacher_watched_his_course'), 'code' => 409];

        if ($episode->userLikes()->where('user_id', auth('api')->id())->exists())
        {
            $episode->userLikes()->detach($user->id);
            $episode->decrement('likes');
            $user->statistics()->where('title->en', 'Granted Likes')->first()->pivot->decrement('progress');
            $episode->course->teacher->statistics()->where('title->en', 'Acquired Likes')->first()->pivot->decrement('progress');
            return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_unliked'), 'code' => 200];
        }
        else
        {
            $episode->userLikes()->attach($user->id);
            $episode->increment('likes');
            $user->statistics()->where('title->en', 'Granted Likes')->first()->pivot->increment('progress');
            $episode->course->teacher->statistics()->where('title->en', 'Acquired Likes')->first()->pivot->increment('progress');
            return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_liked'), 'code' => 200];
        }

    }

    public function downloadFile($episode_id): StreamedResponse|array
    {
        $episode = Episode::query()->find($episode_id);

        $directory = "courses/{$episode->course_id}/episodes/{$episode_id}";

        $file = collect(Storage::disk('local')->files($directory))
            ->first(fn($f) => str_contains(basename($f), 'file'));

        if (!$file) {
            return ['message' => __('msg.file_not_found'), 'code' => 404];
        }

        return Storage::disk('local')->download(
            $file,
            $episode->title . '.' . pathinfo($file, PATHINFO_EXTENSION)
        );
    }
}
