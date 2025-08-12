<?php

namespace App\Services\Episode;

use App\Enums\CourseStatusEnum;
use App\Http\Resources\Episode\EpisodeStudentResource;
use App\Http\Resources\Episode\EpisodeTeacherResource;
use App\Http\Resources\Episode\EpisodeWithDetailsResource;
use App\Models\Course;
use App\Models\Episode;
use App\Traits\BadgeTrait;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EpisodeService
{
    use BadgeTrait;

    public function getToTeacher($course_id): array
    {
        $course = Course::query()
            ->where([
                'teacher_id' => auth('api')->id(),
                'id' => $course_id
            ])
            ->with(['episodes.quiz.questions'])// Eager load relationships
            ->first();

        if (is_null($course)) {
            return ['message' => __('msg.course_not_found'), 'code' => 404];
        }
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

        if(is_null($course)) {
            if(!$user->followed_courses()->where('course_id', $course_id)->exists()) {
                return ['message' => 'Unauthorized access!', 'code' => 403];
            }
            return ['message' => __('msg.course_not_found'), 'code' => 404];
        }
        $episodes['episodes'] = EpisodeStudentResource::collection($course->episodes);
        $episodes['progress_percentage'] = $course->pivot->perc_progress . '%';
        $episodes['num_of_completed_quizzes'] = $course->pivot->num_of_completed_quizzes;
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

        if (!($course->status == 'draft' || $course->status == 'rejected')) {
            return ['message' => __('msg.can_not_add_episode') . $course->status . '!', 'code' => 403];
        }

        $request['course_id'] = $course_id;
        $episode = Episode::query()->create($request);
        $episode_path = "courses/$course->id/episodes/$episode->id";

        $request['image_url']->storeAs($episode_path, 'thumbnail.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video.mp4', 'local');

        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video.mp4")->getDurationInSeconds();

        $episode->update([
            'duration' => $request['duration']
        ]);

        $course->update([
            'num_of_episodes' => $course->num_of_episodes + 1,
            'total_of_time' => $course['total_of_time'] + $request['duration'],
        ]);
        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_created'), 'code' => 201];
    }

    public function update($request, $id): array
    {
        $episode = Episode::query()->find($id);
        if (is_null($episode) ||
            !Course::query()
                ->where([
                    'teacher_id' => auth('api')->id(),
                    'id' => $episode->course_id,
                ])->exists()) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }

        $course = $episode->course;
        if (!($episode->course->status == 'draft' || $episode->course->stauts == 'rejected')) {
            return ['message' => __('msg.can_not_update_episode') . $episode->course->status . '!', 'code' => 403];
        }

        $episode_path = "courses/$course->id/episodes/$episode->id";
        $request['image_url']->storeAs($episode_path, 'thumbnail.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video.mp4', 'local');
        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video.mp4")->getDurationInSeconds();
        $episode->update($request);
        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_updated'), 'code' => 200];
    }

    public function showToTeacher($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);
        if (is_null($episode) ||
            !Course::query()
                ->where([
                    'teacher_id' => $user->id,
                    'id' => $episode->course_id,
                ])->exists()) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }

        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_retrieved'), 'code' => 200];
    }

    public function showToStudent($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);
        if (is_null($episode)) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }

        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_retrieved'), 'code' => 200];
    }

    public function destroy($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);
        if (is_null($episode) || !$user->published_courses()->where('id', $episode->course->id)->exists()) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }

        $course = $episode->course;
        if (!($course->status == 'draft' || $course->status == 'rejected')) {
            return ['message' => __('msg.can_not_delete_episode') . $course->status . '!', 'code' => 403];
        }

        $min_episode = Episode::query()->withTrashed()->where('course_id', $course->id)->orderBy('episode_number')->first();
        $episode->update([
            'episode_number' => $min_episode->episode_number - 1,
        ]);

        $episode->delete();
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
    public function finish_episode($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        if ($user->episodes()->where('episode_id', $id)->exists()) {
            return ['message' => __('msg.episode_watched_before'), 'code' => 409];
        }

        // Episode has been watched
        $user->episodes()->attach($episode);
        $episode->increment('views');
        $episode->course->teacher()->statistics()->where('title->en', 'Acquired Views')->first()->pivot->increment('progress');

        // Update progress in course
        $course = $user->followed_courses()->wherePivot('course_id', $episode->course->id)->first();
        $progress = $course->pivot->increment('progress');
        $course->pivot->update(['perc_progress' => ($course->pivot->progress * 100) / $episode->course->episodes->count()]);

        // Update activity of user
        $user->moreDetail->is_active_today = true;
        $user->moreDetail->save();

        // Check to user end the course
        if ($course->pivot->perc_progress == 100) {
            $course->pivot->update(['episodes_completed' => true]);
            if ($course->pivot->quizzes_completed) {
                $course->pivot->update(['get_certificate' => true]);
                $user->statistics()->where('title->en', 'Num Of Certificates')->first()->pivot->increment('progress');
                $this->checkStatistic($course);
            }
        }

        return ['message' => __('msg.is_active'), 'code' => 200];
    }

    // Add Like to specific episode.
    public function addLikeToEpisode($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        if ($episode->userLikes()->where('user_id', auth('api')->id())->exists()) {
            return ['message' => __('msg.already_liked_episode'), 'code' => 409];
        }

        if (!$user->episodes()->where('episode_id', $episode->id)->exists()) {
            return ['message' => __('msg.must_watch_episode_liked'), 'code' => 403];
        }

        $episode->userLikes()->attach(auth('api')->id());
        $episode->increment('likes');

        $user->statistics()->where('title', 'Granted Likes')->increment('progress');
        $episode->course->teacher()->statistics()->where('title', 'Acquired Likes')->increment('progress');

        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_liked'), 'code' => 200];
    }

    // Remove Like from specific episode.
    public function removeLikeFromEpisode($id): array
    {
        $user = auth('api')->user();
        $episode = Episode::query()->find($id);

        if (!$episode->userLikes()->where('user_id', $user->id)->exists()) {
            return ['message' => __('msg.do_not_have_like_episode'), 'code' => 404];
        }

        $episode->userLikes()->detach($user->id);
        $episode->decrement('likes');
        $user->statistics()->where('title', 'Granted Likes')->decrement('progress');
        $episode->course->teacher()->statistics()->where('title', 'Acquired Likes')->decrement('progress');
        return ['data' => new EpisodeWithDetailsResource($episode), 'message' => __('msg.episode_unliked'), 'code' => 200];
    }

    public function downloadFile($id): BinaryFileResponse|array
    {
        $episode = Episode::query()->find($id);

        if (is_null($episode->file_url)) {
            return ['message' => __('msg.episode_does_not_have_file'), 'code' => 404];
        }

        return response()->download(storage_path('app/public/' . $episode->file_url),
            $episode->title . '.' . pathinfo($episode->file_url, PATHINFO_EXTENSION));
    }
}
