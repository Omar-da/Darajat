<?php

namespace App\Http\Controllers\App;

use App\Enums\CourseStatusEnum;
use App\Http\Resources\Course\Teacher\CourseForTeacherResource;
use App\Http\Resources\Episode\EpisodeTeacherResource;
use App\Http\Resources\Quiz\TeacherQuizResource;
use App\Models\Course;
use App\Models\DraftCourse;
use App\Models\DraftEpisode;
use App\Models\DraftQuiz;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class UpdateCopiedCourseController
{
    public function getCopyOfCourse($course_id)
    {
        // Load course with episodes and quizzes
        $originalCourse = Course::with(['episodes.quiz.questions'])->findOrFail($course_id);

        // Start transaction for data consistency
        DB::beginTransaction();

        try {
            // Prepare and create course draft
            $draftData = $originalCourse->toArray();
            unset($draftData['created_at'], $draftData['admin_id'], $draftData['publishing_request_date'], $draftData['response_date']);

            $copiedCourse = DraftCourse::create($draftData);

            // Copy episodes with quizzes
            $originalCourse->episodes->each(function($episode) use ($copiedCourse) {
                // Copy episode
                $episodeData = $episode->toArray();
                unset($episodeData['course_id']);
                $copiedEpisode = $copiedCourse->draft_episodes()->create($episodeData);

                // Copy quiz for this episode
                if ($episode->quiz->isNotEmpty()) {
                    $episode->quiz->each(function($quiz) use ($copiedEpisode) {
                        $quizData = $quiz->toArray();
                        unset($quizData['id'], $quizData['episode_id'], $quizData['quiz_writing_date']);
                        $copiedQuiz = $copiedEpisode->draft_quiz()->create($quizData);

                        foreach($quiz->questions as $question)
                        {
                            $questionData = $question->toArray();
                            unset($questionData['id'], $questionData['quiz_id']);
                            $copiedQuiz->draft_questions()->create($questionData);
                        }
                    });
                }
            });

            DB::commit();

            // Return fully loaded draft with relationships
            return $copiedCourse;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateCourseCopy(Request $request, DraftCourse $course)
    {
        Storage::disk('public')->delete("img/courses/$course->image_url");
        $request['image_url'] = $request['image_url']->store('img/courses', 'public');
        $course->update($request->all());

        return ['data' => new CourseForTeacherResource($course), 'message' => __('msg.course_updated'), 'code' => 200];
    }

    public function createEpisodeCopy(Request $request, DraftCourse $course)
    {
        // Episode Data
        $origianl_episode = $course->original_course->episodes()->create($request->all());
        $episode = $course->draft_episodes()->create($origianl_episode->toArray());

        // Video, Thumbnail and File
        $episode_path = "courses/$course->id/episodes/$episode->id";
        $request['image_url']->storeAs($episode_path, 'thumbnail_copy.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video_copy.mp4', 'local');
        if(request()->hasFile('file_url'))
            $request['file_url']->storeAs($episode_path, 'file_copy.' . $request['file_url']->getClientOriginalExtension(), 'local');

        // Duration
        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video_copy.mp4")->getDurationInSeconds();
        $episode->update([
            'duration' => $request['duration']
        ]);

        // Related Course
        $course->update([
            'num_of_episodes' => $course->num_of_episodes + 1,
            'total_time' => $course['total_time'] + $request['duration'],
        ]);

        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_created'), 'code' => 201];
    }


    public function updateEpisodeCopy(Request $request, DraftEpisode $episode)
    {
        $course = $episode->draft_course;

        // Video, Thumbnail and File
        $episode_path = "courses/$course->id/episodes/$episode->id";
        $request['image_url']->storeAs($episode_path, 'thumbnail_copy.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video_copy.mp4', 'local');
        if(request()->hasFile('file_url'))
            $request['file_url']->storeAs($episode_path, 'file_copy.' . $request['file_url']->getClientOriginalExtension(), 'local');
        else
           Storage::disk('local')->delete($this->get_full_path($episode_path, 'file_copy.'));

        // Duration
        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video_copy.mp4")->getDurationInSeconds();

        // Update
        $episode->update($request->all());

        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_updated'), 'code' => 200];
    }

    public function destroyEpisodeCopy(DraftEpisode $episode)
    {
        $course = $episode->draft_course;

        // Update numbers of episodes
        foreach ($course->draft_episodes as $episode_from_course)
            if ($episode_from_course->episode_number > $episode->episode_number)
                $episode_from_course->decrement('episode_number');

        // Video, Thumbnail and File
        $episode_path = "courses/$course->id/episodes/$episode->id";
        Storage::disk('local')->delete("$episode_path/video_copy.mp4");
        Storage::disk('local')->delete("$episode_path/thumbnail_copy.jpg");
        Storage::disk('local')->delete($this->get_full_path($episode_path, 'file_copy.'));


        if($episode->draft_quiz->exists())
            $course->decrement('total_quizzes');
        $course->decrement('num_of_episodes');
        $course->update([
            'total_time' => $course->total_time - $episode->duration
        ]);
        $episode->delete();

        return ['message' => __('msg.episode_deleted'), 'code' => 200];
    }

    public function createQuizCopy(Request $request, DraftEpisode $episode)
    {
        // Check if quiz already exists
        if($episode->quiz->exists())
            return ['message' => __('msg.quiz_already_exists'), 'code' => 409];

        // Create quiz
        $quiz = DraftQuiz::create([
            'episode_id' => $episode->id,
            'num_of_questions' => $request['num_of_questions'],
        ]);

        // Create questions
        $quiz->questions()->createMany($request['questions']);

        // Increment total quizzes
        $episode->draft_course->increment('total_quizzes');

        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_created'), 'code' => 201];
    }

    public function updateQuizCopy(Request $request, DraftQuiz $quiz)
    {
        // Delete old questions
        $quiz->draft_questions()->delete();

        // Create new questions
        $quiz->draft_questions()->createMany($request['questions']);

        // Update num of questions
        $quiz->update([
            'num_of_questions' => $request['num_of_questions'],
        ]);

        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_updated'), 'code' => 201];
    }

    public function destroyQuizCopy(DraftQuiz $quiz)
    {
        $quiz->draft_episode->draft_course->decrement('total_quizzes');
        $quiz->delete();

        return ['message' => __('msg.quiz_deleted'), 'code' => 200];
    }

    public function repostCourse($draft_course_id)
    {
        DB::transaction(function () use ($draft_course_id)
        {
            // 1. Load all draft data at once
            $draft = DraftCourse::with('draft_episodes.draft_quiz.draft_questions')->findOrFail($draft_course_id);
            $original = Course::findOrFail($draft->id);

            // 2. Update course (exclude draft-specific fields)
            Storage::disk('public')->delete("img/courses/{$original->image_url}");
            $original->update($draft);

            // 3. Replace old files
            $original->episodes()->each(function ($episode) use ($original)
            {
                $episode_path = "courses/$original->id/episodes/$episode->id";
                if(DraftEpisode::where('id', $episode->id)->exists())
                {
                    Storage::disk('local')->move("$episode_path/video_copy.mp4", "$episode_path/video.mp4");
                    Storage::disk('local')->move("$episode_path/thumbnail_copy.jpg", "$episode_path/thumbnail.jpg");
                    Storage::disk('local')->move($this->get_full_path($episode_path, 'file_copy.'), $this->get_full_path($episode_path, 'file.'));
                }
                else
                {
                    Storage::disk('local')->deleteDirectory($episode_path);
                }

                $episode->delete();            // Then delete episode
            });

            // 4. Copy all new content
            foreach ($draft->draft_episodes as $episode)
            {
                $newEpisode = $original->episodes()->create($episode->except(['draft_course_id']));

                if($episode->draft_quiz->exists())
                {
                    $quiz = $episode->draft_quiz;
                    $newQuiz = $newEpisode->quiz()->create($quiz->except(['id', 'draft_episode_id']));
                    $newQuiz->questions()->createMany($quiz->questions->map->except(['id', 'draft_quiz_id']));
                }
            }

            // 5. Cleanup
            $draft->delete();

            // 6. Repost Course
            $original->update([
                'status' => CourseStatusEnum::PENDING,
                'publishing_request_date' => now()->format('Y-m-d H:i:s')
            ]);

            return $original;
        });
    }

    public function cancel(DraftCourse $course)
    {
        foreach($course->draft_episodes as $episode)
        {
            $episode_path = "courses/$course->id/episodes/$episode->id";
            Storage::disk('local')->delete("$episode_path/video_copy.mp4");
            Storage::disk('local')->delete("$episode_path/thumbnail_copy.jpg");
            Storage::disk('local')->delete($this->get_full_path($episode_path, 'file_copy.'));
        }

        $course->delete();

        return response()->json([
            'message' => 'Changes deleted successfully'
        ], 200);
    }

    public function get_full_path($base_path, $file_name)
    {
        $file = collect(Storage::disk('local')->files($base_path))->first(fn($f) => str_contains(basename($f), $file_name));
        if(!is_null($file))
            return "$base_path/$file_name" . pathinfo($file, PATHINFO_EXTENSION);
    }
}
