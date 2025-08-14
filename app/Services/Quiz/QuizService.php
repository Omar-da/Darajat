<?php

namespace App\Services\Quiz;

use App\Http\Resources\Quiz\ResultResource;
use App\Http\Resources\Quiz\TeacherQuizResource;
use App\Models\Episode;
use App\Models\Question;
use App\Models\Quiz;
use App\Traits\BadgeTrait;
use Illuminate\Support\Facades\Gate;

class QuizService
{
    use BadgeTrait;

    public function store($episode_id, $request): array
    {
        $episode = Episode::query()->find($episode_id);

        if(is_null($episode)){
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }

        if(!is_null($episode->quiz)) {
            return ['message' => __('msg.quiz_already_exists'), 'code' => 409];
        }

        Gate::authorize('quizAction', $episode->course);
        $quiz = Quiz::query()->create([
            'episode_id' => $episode_id,
            'num_of_questions' => $request['num_of_questions'],
        ]);

        $quiz->questions()->createMany($request['questions']);
        $quiz->episode->course->increment('total_quizzes');

        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_created'), 'code' => 201];
    }

    public function processAnswer($request): array
    {
        $user = auth('api')->user();
        $quiz = Quiz::query()->find($request['quiz_id']);

        $question = $quiz->questions()->where('question_number', $request['question_number'])->first();
        if (is_null($question)) {
            return ['message' => __('msg.question_not_found'), 'code' => 404];
        }

        Gate::authorize('haveAccess', $quiz);

        if ($question->right_answer == $request['answer']) {
            $data['is_correct'] = true;
            $message = __('msg.correct_answer.great_job') . $user['first_name'] . __('msg.correct_answer.correct');
        } else {
            $data['is_correct'] = false;
            $message = __('msg.incorrect_answer.oops') . $user['first_name'] . __('msg.incorrect_answer.incorrect');
        }
        $data['right_answer'] = $question->right_answer;
        $data['explanation'] = $question->explanation;
        return ['data' => $data, 'message' => $message, 'code' => 200];
    }

    public function calculateQuizResult($quiz_id, $request): array
    {
        $user = auth('api')->user();
        $quiz = Quiz::query()->find($quiz_id);
        if (is_null($quiz)) {
            return ['message' => __('msg.quiz_not_found'), 'code' => 404];
        }

        Gate::authorize('haveAccess', $quiz);

        if (count($request) < $quiz->num_of_questions) {
            return ['message' => __('msg.not_answered_all_questions'), 'code' => 403];
        }

        $quiz_user = $user->quizzes()->find($quiz_id);
        if(is_null($quiz_user)) {
            $user->quizzes()->attach($quiz_id);
        } else if($quiz_user->pivot->success) {
            return ['message' => __('msg.already_succeeded_quiz'), 'code' => 409];
        }

        $quiz_user = $user->quizzes()->find($quiz_id);
        $mark = 0;
        foreach ($request as $question) {
            if ($question['answer'] == Question::query()->findOrFail($question['question_id'])->right_answer) {
                $mark++;
            }
        }
        $percentage_mark = round($mark / count($request) * 100, 2);
        $quiz_user->pivot->update(
            [
                'mark' => $mark,
                'percentage_mark' => $percentage_mark,
                'success' => $percentage_mark >= 60 ? 1 : 0,
            ]);
        $result = new ResultResource($quiz_user);
        if ($result['success']) {
            // Update the episode quiz status to mark that the user has passed the quiz
            $episode = $user->episodes()->where('episode_id', $quiz->episode_id)->first();
            $episode->pivot->pass_quiz = true;
            $episode->pivot->save();

            // Update the course quiz to increment field num_of_completed_quizzes
            $course = $user->followed_courses()->where('course_id', $quiz->episode->course_id)->first();
            $course->pivot->increment('num_of_completed_quizzes');

            if ($course->pivot->num_of_completed_quizzes == $course->total_quizzes) {
                $course->pivot->update(['quizzes_completed' => true]);
                if ($course->pivot->episodes_completed) {
                    $this->checkStatistic($course);
                }
            }

            $message = __('msg.result_success.cong') . $user['first_name'] . __('msg.result_success.passed');
        } else {
            $message = __('msg.result_failed');
        }
        return ['data' => $result, 'message' => $message, 'code' => 200];
    }

    public function update($quiz_id, $request): array
    {
        $user = auth('api')->user();
        $quiz = Quiz::query()->find($quiz_id);
        if (is_null($quiz)) {
            return ['message' => __('msg.quiz_not_found'), 'code' => 404];
        }

        Gate::authorize('quizAction', $quiz->episode->course);

        $quiz->questions()->delete();
        $quiz->update([
            'num_of_questions' => $request['num_of_questions'],
        ]);
        $quiz->questions()->createMany($request['questions']);

        return ['data' => new TeacherQuizResource($quiz), 'message' => __('msg.quiz_updated'), 'code' => 201];
    }

    public function destroy($id): array
    {
        $quiz = Quiz::query()->find($id);
        if (is_null($quiz)) {
            return ['message' => __('msg.quiz_not_found'), 'code' => 404];
        }

        Gate::authorize('quizAction', $quiz->episode->course);

        $quiz->delete();

        return ['message' => __('msg.quiz_deleted'), 'code' => 200];
    }
}
