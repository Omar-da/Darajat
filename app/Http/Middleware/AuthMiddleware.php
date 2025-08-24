<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Firebase\FirebaseOAuth;

class AuthMiddleware
{
    protected $firebase;

    public function __construct(FirebaseOAuth $firebase)
    {
        $this->firebase = $firebase;
    }

    public function handle($request, Closure $next)
    {
        $idToken = $request->bearerToken();
        
        $isTokenValid = $idToken && $this->firebase->verifyToken($idToken);
        $isUserAuthenticated = auth('api')->user();

        if (!$isTokenValid && !$isUserAuthenticated)
            return response()->json(['error' => 'Unauthorized'], 401);

        return $next($request);
    }
}