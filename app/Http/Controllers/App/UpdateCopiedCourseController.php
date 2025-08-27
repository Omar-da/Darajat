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
use Symfony\Component\HttpFoundation\StreamedResponse;

class UpdateCopiedCourseController extends Controller
{
    public function getCopyOfCourse($course_id)
    {
        // Load course with episodes and quizzes
        $originalCourse = Course::with(['episodes.quiz.questions'])->findOrFail($course_id);

        // Start transaction for data consistency
        DB::beginTransaction();
        // Prepare and create course draft
        $draftData = $originalCourse->toArray();
        unset($draftData['created_at'], $draftData['admin_id'], $draftData['publishing_request_date'], $draftData['response_date']);
        $draftData['original_course_id'] = $originalCourse->id;
        $copiedCourse = DraftCourse::create($draftData);


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
            $episodeData = $episode->toArray();
            unset($episodeData['course_id']);
            $copiedEpisode = $copiedCourse->draft_episodes()->create($episodeData);

            // Copy quiz for this episode
            if ($episode->quiz()->exists()) 
            {
                $quiz = $episode->quiz;
                $quizData = $quiz->toArray();
                unset($quizData['id'], $quizData['episode_id'], $quizData['quiz_writing_date']);
                $copiedQuiz = $copiedEpisode->draft_quiz()->create($quizData);
                foreach ($quiz->questions as $question) {
                    $questionData = $question->getAttributes();
                    unset($questionData['id'], $questionData['quiz_id']);
                    $questionData['question_number'] = $copiedQuiz->draft_questions()->count() + 1;
                    $copiedQuiz->draft_questions()->create($questionData);
                }
            }
        }
        DB::commit();
        
        // Return fully loaded draft with relationships
        return ['data' => new CourseWithDetailsForTeacherResource($copiedCourse), 'message' => __('msg.course_retrieved'), 'code' => 200];
    }

    public function updateCourseCopy(UpdateDraftCourse $request, $course_id)
    {
        DB::beginTransaction();
        $course = DraftCourse::findOrFail($course_id);
        $image_url = ['image_url' => $course->image_url];

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
        
        $course->update(array_merge($request->all(), $image_url));

        DB::commit();
        
        return ['data' => new CourseForTeacherResource($course), 'message' => __('msg.course_updated'), 'code' => 200];
    }

    public function createEpisodeCopy(CreateEpisodeRequest $request, $course_id)
    {
        DB::beginTransaction();
        $course = DraftCourse::findOrFail($course_id);

        // Episode Data
        $request['episode_number'] = $course->num_of_episodes + 1;
        $request['is_copied_episode'] = true;
        $origianl_episode = $course->original_course->episodes()->create($request->all());
        $episode = $course->draft_episodes()->create(Arr::except($origianl_episode->toArray(), ['is_copied_episode']));

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

        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_created'), 'code' => 201];
    }


    public function updateEpisodeCopy(UpdateEpisodeRequest $request, $episode_id)
    {
        DB::beginTransaction();
        $episode = DraftEpisode::findOrFail($episode_id);
        $course = $episode->draft_course;

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

        // Update
        $episode->update($request->all());
        DB::commit();
        
        return ['data' => new EpisodeTeacherResource($episode), 'message' => __('msg.episode_updated'), 'code' => 200];
    }

    public function destroyEpisodeCopy($episode_id)
    {
        DB::beginTransaction();
        $episode = DraftEpisode::findOrFail($episode_id);
        $course = $episode->draft_course;

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
        
        if ($episode->draft_quiz()->exists())
            $course->decrement('total_quizzes');
        $course->decrement('num_of_episodes');
        $course->update([
            'total_time' => $course->total_time - $episode->duration
        ]);

        Episode::where(['id' => $episode->id, 'is_copied_episode' => true])->delete();
        $episode->delete();

        DB::commit();
        return ['message' => __('msg.episode_deleted'), 'code' => 200];
    }   

    public function createQuizCopy(CreateQuizRequest $request, $episode_id)
    {
        DB::beginTransaction();
        
        $episode = DraftEpisode::findOrFail($episode_id);

        // Check if quiz already exists
        if ($episode->draft_quiz()->exists())
            return ['message' => __('msg.quiz_already_exists'), 'code' => 409];

        // Create quiz
        $quiz = DraftQuiz::create([
            'draft_episode_id' => $episode->id,
            'num_of_questions' => $request['num_of_questions'],
        ]);

        // Create questions
        foreach($request['questions'] as $question)
        {
            $question['question_number'] = $quiz->draft_questions()->count() + 1;
            $quiz->draft_questions()->create($question);
        }

        // Increment total quizzes
        $episode->draft_course->increment('total_quizzes');

        DB::commit();
        
        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_created'), 'code' => 201];
    }

    public function updateQuizCopy(UpdateQuizRequest $request, $quiz_id)
    {
        DB::beginTransaction();
        $quiz = DraftQuiz::findOrFail($quiz_id);

        // Delete old questions
        $quiz->draft_questions()->delete();

        // Create questions
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
        
        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_updated'), 'code' => 201];
    }

    public function destroyQuizCopy($quiz_id)
    {
        DB::beginTransaction();
        $quiz = DraftQuiz::findOrFail($quiz_id);
        $quiz->draft_episode->draft_course->decrement('total_quizzes');
        $quiz->delete();
        DB::commit();

        return ['message' => __('msg.quiz_deleted'), 'code' => 200];
    }

    public function repostCourse($course_id)
    {
        DB::beginTransaction();
            // 1. Load all draft data at once
            $draft = DraftCourse::with('draft_episodes.draft_quiz.draft_questions')->findOrFail($course_id);
            $original = $draft->original_course;

            // 2. Update course (exclude draft-specific fields)
            Storage::disk('uploads')->delete("courses/$original->image_url");
            $original->update(Arr::except($draft->toArray(), ['original_course']));

            // 3. Replace old files
            $original->episodes()->each(function ($original_episode) use ($original) {
                $episode_path = "courses/$original->id/episodes/$original_episode->id";
                if (DraftEpisode::where('id', $original_episode->id)->exists()) 
                {
                    $draft_episode = DraftEpisode::findOrFail($original_episode->id);
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

                    $original_episode->update(Arr::except($draft_episode->toArray(), ['course_id']));

                    if($original_episode->quiz()->exists())
                        $original_episode->quiz()->delete();
                    if($draft_episode->draft_quiz()->exists())
                    {
                        $draft_quiz = $draft_episode->draft_quiz;
                        $quizData = Arr::except($draft_quiz->toArray(), ['id', 'draft_episode_id']);
                        $newQuiz = $original_episode->quiz()->create($quizData);
                        foreach($draft_quiz->draft_questions as $draft_question)
                        {
                            $questionData = Arr::except($draft_question->getAttributes(), ['id', 'draft_quiz_id']);
                            $questionData['question_number'] = $newQuiz->questions()->count() + 1;
                            $newQuiz->questions()->create($questionData);
                        }
                    }
                }
                else 
                {
                    Storage::disk('local')->deleteDirectory($episode_path);
                    $original_episode->delete();
                }

            });
                
            // 5. Cleanup
            $draft->delete();

            // 6. Repost Course
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
        $course = DraftCourse::findOrFail($course_id);

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
