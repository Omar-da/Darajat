<?php

namespace App\Http\Controllers\App;

use App\Http\Requests\Quiz\AnswerRequest;
use App\Http\Requests\Quiz\CreateQuizRequest;
use App\Http\Requests\Quiz\ResultRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use App\Responses\Response;
use App\Services\Quiz\QuizService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class QuizController extends Controller
{
    private QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    // For Teacher
    public function store($episode_id, CreateQuizRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->quizService->store($episode_id, $request->validated());
            if ($data['code'] == 404 || $data['code'] == 409) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            if ($th instanceof AuthorizationException) {
                return Response::error($message, $th->getCode());
            }
            return Response::error($message);
        }
    }

    // For Student
    public function processAnswer(AnswerRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->quizService->processAnswer($request->validated());
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            if ($th instanceof AuthorizationException) {
                return Response::error($message, $th->getCode());
            }
            return Response::error($message);
        }
    }

    // For Student
    public function calculateQuizResult($quiz_id, ResultRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->quizService->calculateQuizResult($quiz_id, $request->validated());
            if ($data['code'] == 404 || $data['code'] == 403 || $data['code'] == 409) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            if ($th instanceof AuthorizationException) {
                return Response::error($message, $th->getCode());
            }
            return Response::error($message);
        }
    }

    // For Teacher
    public function update($quiz_id, UpdateQuizRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->quizService->update($quiz_id, $request->validated());
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            if ($th instanceof AuthorizationException) {
                return Response::error($message, $th->getCode());
            }
            return Response::error($message);
        }
    }

    // For Teacher
    public function destroy($quiz_id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->quizService->destroy($quiz_id);
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success([], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            if ($th instanceof AuthorizationException) {
                return Response::error($message, $th->getCode());
            }
            return Response::error($message);
        }
    }

}
