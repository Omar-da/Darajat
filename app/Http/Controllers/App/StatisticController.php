<?php

namespace App\Http\Controllers\App;

use App\Http\Resources\Statistic\StatisticResource;
use App\Responses\Response;
use Illuminate\Http\JsonResponse;

class StatisticController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth('api')->user();

        $statistics = $user->statistics;

        return Response::success(StatisticResource::collection($statistics), __('msg.statistics'));
    }
}
