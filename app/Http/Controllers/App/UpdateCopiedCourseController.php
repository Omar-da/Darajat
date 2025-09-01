<?php

namespace App\Http\Controllers\App;

use App\Enums\CourseStatusEnum;
use App\Http\Requests\Course\UpdateDraftCourse;
use App\Http\Requests\Episode\CreateEpisodeRequest;
use App\Http\Requests\Episode\UpdateEpisodeRequest;
use App\Http\Requests\Quiz\CreateQuizRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use App\Http\Resources\Course\Teacher\CourseForTeacherResource;
use App\Http\Resources\Course\Teacher\CourseWithDetailsForTeacherResource;
use App\Http\Resources\Episode\EpisodeTeacherResource;
use App\Http\Resources\Quiz\TeacherQuizResource;
use App\Models\Course;
use App\Models\DraftCourse;
use App\Models\DraftEpisode;
use App\Models\DraftQuiz;
use App\Models\Episode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class UpdateCopiedCourseController extends Controller
{
    public function getCopyOfCourse($course_id)
    {
        // Load course with episodes, quizzes and questions
        $originalCourse = Course::with(['episodes.quiz.questions'])->findOrFail($course_id);

        DB::beginTransaction();

        // Copy course
        $copiedCourse = DraftCourse::create([
            ...Arr::except($originalCourse->toArray(), [
                'created_at', 'admin_id', 'publishing_request_date', 'response_date'
            ]),
            'original_course_id' => $originalCourse->id
        ]);

        // Copy episodes with quizzes
        foreach($originalCourse->episodes as $episode)
        {
            $episode_path = "courses/$originalCourse->id/episodes/$episode->id";
            Storage::disk('local')->copy("$episode_path/video.mp4", "$episode_path/video_copy.mp4");
            Storage::disk('local')->copy("$episode_path/thumbnail.jpg", "$episode_path/thumbnail_copy.jpg");
            $original_file_path = $this->get_full_path($episode_path, 'file.');
            if ($original_file_path)
                Storage::disk('local')->copy($original_file_path, str_replace('file', 'file_copy', $original_file_path));

            // Copy episode
            $copiedEpisode = $copiedCourse->draft_episodes()->create(
                Arr::except($episode->toArray(), ['course_id'])
            );

            // Copy quiz for this episode
            if ($episode->quiz()->exists()) 
            {
                $quiz = $episode->quiz;
                $copiedQuiz = $copiedEpisode->draft_quiz()->create(
                    Arr::except($quiz->toArray(), ['id', 'episode_id', 'quiz_writing_date'])
                );
                foreach ($quiz->questions as $question)
                    $copiedQuiz->draft_questions()->create([
                    ...Arr::except($question->getAttributes(), ['id', 'quiz_id']),
                    'question_number' => $copiedQuiz->draft_questions()->count() + 1
                ]);
            }
        }

        DB::commit();
        
        return ['data' => new CourseWithDetailsForTeacherResource($copiedCourse), 'message' => __('msg.course_retrieved'), 'code' => 200];
    }

    public function updateCourseCopy(UpdateDraftCourse $request, $course_id)
    {
        $course = DraftCourse::findOrFail($course_id);

        DB::beginTransaction();

        $image_url = ['image_url' => $course->image_url];

        // Update image
        if ($request->hasFile('image_url')) 
        {
            $file = $request->file('image_url');
            
            // Generate a proper filename
            $extension = $file->getClientOriginalExtension();
            $filename = uniqid() . '.' . $extension;
            
            // Store the file properly
            $file->storeAs('courses', $filename, 'uploads');
            $image_url = ['image_url' => $filename];
        }
        
        // Update Coursee
        $course->update(array_merge($request->all(), $image_url));

        DB::commit();

        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);
        
        return ['data' => new CourseForTeacherResource($course), 'message' => __('msg.course_updated'), 'code' => 200];
    }

    public function createEpisodeCopy(CreateEpisodeRequest $request, $course_id)
    {
        $course = DraftCourse::with(['original_course.episodes', 'draft_episodes'])->findOrFail($course_id);
        
        DB::beginTransaction();

        // Create an Original Episode and Copy it
        $original_episode = $course->original_course->episodes()->create([
            ...$request->all(),
            'episode_number' => $course->num_of_episodes + 1,
            'is_copied_episode' => true
        ]);
        $episode = $course->draft_episodes()->create(Arr::except($original_episode->toArray(), ['is_copied_episode']));

        // Video, Thumbnail and File
        $episode_path = "courses/$course->id/episodes/$episode->id";
        $request['image_url']->storeAs($episode_path, 'thumbnail_copy.jpg', 'local');
        $request['video_url']->storeAs($episode_path, 'video_copy.mp4', 'local');
        if (request()->hasFile('file_url'))
            $request['file_url']->storeAs($episode_path, 'file_copy.' . $request['file_url']->getClientOriginalExtension(), 'local');

        // Duration
        $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video_copy.mp4")->getDurationInSeconds();
        $episode->update([
            'duration' => $request['duration']
        ]);

        // Related Course
        $course->update([
            'total_time' => $course['total_time'] + $request['duration'],
            'num_of_episodes' => $course->num_of_episodes + 1
        ]);

        DB::commit();

        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);

        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_created'), 'code' => 201];
    }


    public function updateEpisodeCopy(UpdateEpisodeRequest $request, $episode_id)
    {
        $episode = DraftEpisode::with('draft_course')->findOrFail($episode_id);
        $course = $episode->draft_course;

        DB::beginTransaction();

        // Video, Thumbnail and File
        $episode_path = "courses/$course->id/episodes/$episode->id";
        if($request['image_url'])
            $request['image_url']->storeAs($episode_path, 'thumbnail_copy.jpg', 'local');
        if($request['video_url'])
        {
            $request['video_url']->storeAs($episode_path, 'video_copy.mp4', 'local');
            $request['duration'] = FFMpeg::fromDisk('local')->open("$episode_path/video_copy.mp4")->getDurationInSeconds();
        }
        if (request()->hasFile('file_url'))
        {
            $file_path = $this->get_full_path($episode_path, 'file_copy.');
            if ($file_path)
                Storage::disk('local')->delete($file_path);
            $request['file_url']->storeAs($episode_path, 'file_copy.' . $request['file_url']->getClientOriginalExtension(), 'local');
        }

        // Update episode
        $episode->update($request->all());

        DB::commit();
        
        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);

        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_updated'), 'code' => 200];
    }

    public function destroyEpisodeCopy($episode_id)
    {
        $episode = DraftEpisode::with(['draft_course.draft_episodes', 'draft_quiz'])->findOrFail($episode_id);
        $course = $episode->draft_course;

        DB::beginTransaction();

        // Update numbers of episodes
        $old_episode_number = $episode->episode_number;
        $episode->episode_number = 0; 
        $episode->save();
        foreach ($course->draft_episodes as $episode_from_course)
            if ($episode_from_course->episode_number > $old_episode_number)
            {
                $episode_from_course->decrement('episode_number');
                $episode->save();
            }

        // Video, Thumbnail and File
        $episode_path = "courses/$course->id/episodes/$episode->id";
        Storage::disk('local')->delete("$episode_path/video_copy.mp4");
        Storage::disk('local')->delete("$episode_path/thumbnail_copy.jpg");
        $file_path = $this->get_full_path($episode_path, 'file_copy.');
        if ($file_path)
            Storage::disk('local')->delete($file_path);
        if (Storage::disk('local')->exists($episode_path)) 
            $files = Storage::disk('local')->allFiles($episode_path);
        if (empty($files)) 
            Storage::disk('local')->deleteDirectory($episode_path);
        
        // Related Course
        if ($episode->draft_quiz()->exists())
            $course->decrement('total_quizzes');
        $course->decrement('num_of_episodes');
        $course->update([
            'total_time' => $course->total_time - $episode->duration
        ]);

        // Delete the episode
        Episode::where(['id' => $episode->id, 'is_copied_episode' => true])->delete();
        $episode->delete();

        DB::commit();

        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);

        return ['message' => __('msg.episode_deleted'), 'code' => 200];
    }   

    public function createQuizCopy(CreateQuizRequest $request, $episode_id)
    {
        $episode = DraftEpisode::with(['draft_course', 'draft_quiz'])->findOrFail($episode_id);
        $course = $episode->draft_course;

        DB::beginTransaction();

        // Check if quiz already exists
        if ($episode->draft_quiz()->exists())
            return ['message' => __('msg.quiz_already_exists'), 'code' => 409];

        // Create the quiz
        $quiz = DraftQuiz::create([
            'draft_episode_id' => $episode->id,
            'num_of_questions' => $request['num_of_questions'],
        ]);

        // Create the questions
        foreach($request['questions'] as $question)
        {
            $question['question_number'] = $quiz->draft_questions()->count() + 1;
            $quiz->draft_questions()->create($question);
        }

        // Related Course
        $episode->draft_course->increment('total_quizzes');

        DB::commit();
        
        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);

        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_created'), 'code' => 201];
    }

    public function updateQuizCopy(UpdateQuizRequest $request, $quiz_id)
    {
        $quiz = DraftQuiz::with(['draft_episodes.draft_course', 'draft_questions'])->findOrFail($quiz_id);
        $course = $quiz->draft_episode->draft_course;

        DB::beginTransaction();
        
        // Delete old questions
        $quiz->draft_questions()->delete();

        // Create new questions
        foreach($request['questions'] as $question)
        {
            $question['question_number'] = $quiz->draft_questions()->count() + 1;
            $quiz->draft_questions()->create($question);
        }

        // Update num of questions
        $quiz->update([
            'num_of_questions' => $request['num_of_questions'],
        ]);
        
        DB::commit();
        
        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);

        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_updated'), 'code' => 201];
    }

    public function destroyQuizCopy($quiz_id)
    {
        $quiz = DraftQuiz::with('draft_episode.draft_course')->findOrFail($quiz_id);
        $course = $quiz->draft_episode->draft_course;
        
        DB::beginTransaction();

        // Related course
        $quiz->draft_episode->draft_course->decrement('total_quizzes');

        // Delete the quiz
        $quiz->delete();

        DB::commit();

        // Set if there is a modifying
        $course->update([
            'was_edited' => true
        ]);

        return ['message' => __('msg.quiz_deleted'), 'code' => 200];
    }

    public function repostCourse($course_id)
    {
        // Load all draft data
        $draft = DraftCourse::with(['draft_episodes.draft_quiz.draft_questions', 'origianl_course.episodes.quiz'])->findOrFail($course_id);
        $original = $draft->original_course;
        
        // Check if there is a modifying
        if(!$draft->was_edited)
            return response()->json([
                'message' => __('msg.can_not_repost_course'),
            ], 409);

        DB::beginTransaction();

        // Update course
        Storage::disk('uploads')->delete("courses/$original->image_url");
        $original->update(Arr::except($draft->toArray(), ['original_course', 'was_edited']));

        // Replace old files
        $original->episodes()->each(function ($original_episode) use ($original, $draft) {
            $episode_path = "courses/$original->id/episodes/$original_episode->id";
            $draft_episode = $draft->draft_episodes()->find($original_episode->id);
            if ($draft_episode) 
            {
                Storage::disk('local')->move("$episode_path/video_copy.mp4", "$episode_path/video.mp4");
                Storage::disk('local')->move("$episode_path/thumbnail_copy.jpg", "$episode_path/thumbnail.jpg");
                $copied_file_path = $this->get_full_path($episode_path, 'file_copy.');
                $original_file_path = $this->get_full_path($episode_path, 'file.');
                if ($copied_file_path)
                {
                    if($original_file_path)
                        Storage::disk('local')->delete($original_file_path);
                    Storage::disk('local')->move($copied_file_path, str_replace('file_copy', 'file', $copied_file_path));
                }
                else if($original_file_path)
                    Storage::disk('local')->delete($original_file_path);

                // Update the episodes
                $original_episode->update(Arr::except($draft_episode->toArray(), ['course_id']));

                // Delete the old quiz
                $original_episode->quiz()->deleteIfExists();

                // Create a new quiz if it is existed
                if($draft_episode->draft_quiz()->exists())
                {
                    $draft_quiz = $draft_episode->draft_quiz;
                    $newQuiz = $original_episode->quiz()->create(
                        Arr::except($draft_episode->draft_quiz->toArray(), ['id', 'draft_episode_id'])
                    );

                    // Create questions
                    foreach($draft_quiz->draft_questions as $draft_question)
                    {
                        $newQuiz->questions()->create([
                            ...Arr::except($draft_question->getAttributes(), ['id', 'draft_quiz_id']),
                            'question_number' => $newQuiz->questions()->count() + 1
                        ]);
                    }
                }
            }
            else 
            {
                Storage::disk('local')->deleteDirectory($episode_path);
                $original_episode->delete();
            }

        });
            
        // Delete draft data
        $draft->delete();

        // Repost course
        $original->update([
            'status' => CourseStatusEnum::PENDING,
            'publishing_request_date' => now()->format('Y-m-d')
        ]);

        DB::commit();
        
        return new CourseWithDetailsForTeacherResource($original);
    }

    public function cancel($course_id)
    {
        DB::beginTransaction();

        $course = DraftCourse::with(['draft_episodes', 'original_course.episodes'])->findOrFail($course_id);

        // Delete the copied files
        foreach ($course->draft_episodes as $episode) {
            $episode_path = "courses/$course->id/episodes/$episode->id";
            Storage::disk('local')->delete("$episode_path/video_copy.mp4");
            Storage::disk('local')->delete("$episode_path/thumbnail_copy.jpg");
            $file_path = $this->get_full_path($episode_path, 'file_copy.');
            if ($file_path)
                Storage::disk('local')->delete($file_path);
            if (Storage::disk('local')->exists($episode_path)) 
                $files = Storage::disk('local')->allFiles($episode_path);
            if (empty($files)) 
                Storage::disk('local')->deleteDirectory($episode_path);
        }

        // Delete the copied course
        $course->original_course->episodes()->where('is_copied_episode', true)->delete();
        $course->delete();

        DB::commit();

        return response()->json([
            'message' => 'Changes deleted successfully'
        ], 200);
    }

    public function get_full_path($base_path, $file_name)
    {
        $file = collect(Storage::disk('local')->files($base_path))->first(fn($f) => str_contains(basename($f), $file_name));
        if (!is_null($file))
            return "$base_path/$file_name" . pathinfo($file, PATHINFO_EXTENSION);
    }
}
