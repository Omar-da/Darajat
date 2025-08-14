<?php

namespace App\Http\Controllers\App;

use App\Models\Speciality;
use App\Responses\Response;
use Illuminate\Http\JsonResponse;

class SpecialityController extends Controller
{
    public function index(): JsonResponse
    {
        $specialities = Speciality::all();
        return Response::success($specialities, __('msg.specialities_retrieved'));
    }
}
