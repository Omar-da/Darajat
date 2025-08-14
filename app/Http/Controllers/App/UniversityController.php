<?php

namespace App\Http\Controllers\App;

use App\Models\University;
use App\Responses\Response;
use Illuminate\Http\JsonResponse;

class UniversityController extends Controller
{
    public function index(): JsonResponse
    {
        $universities = University::all();
        return Response::success($universities, __('msg.universities_retrieved'));
    }
}
