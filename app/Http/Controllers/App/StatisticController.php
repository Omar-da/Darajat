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

    public function getEnthusiasm()
    {
        $statistics = auth('api')->user()->statistics;
        $current_enthusiasm = $statistics->findOrfail(1);
        $max_enthusiasm = $statistics->findOrfail(2);

        return Response::success(StatisticResource::collection([$current_enthusiasm, $max_enthusiasm]), __('msg.statistics'));
    }
}
