<?php

namespace App\Http\Controllers\App;

use App\Http\Resources\Badge\BadgeResource;
use App\Responses\Response;
use Illuminate\Http\JsonResponse;

class BadgeController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $bronzeBadges = $user->badges()->where('level', 1)->get();
        $silverBadges = $user->badges()->where('level', 2)->get();
        $goldBadges = $user->badges()->where('level', 3)->get();

        return Response::success([
            'bronze' => BadgeResource::collection($bronzeBadges),
            'silver' => BadgeResource::collection($silverBadges),
            'gold' => BadgeResource::collection($goldBadges)],
            __('msg.badges_retrieved'));
    }
}
