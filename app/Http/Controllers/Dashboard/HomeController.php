<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\App\Controller;
use App\Models\PlatformStatistics;

class HomeController extends Controller
{
    public function index()
    {
        $platform_stats = PlatformStatistics::getStats();

        return view('home', $platform_stats);
    }
}
