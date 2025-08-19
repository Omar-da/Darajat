<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\App\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class WebAuthController extends Controller
{

    // Register

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Login
    
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('dashboard.login');
    }
}
