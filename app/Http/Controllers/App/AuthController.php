<?php

namespace App\Http\Controllers\App;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Responses\Response;
use App\Services\Firebase\FirebaseOAuth;
use App\Services\User\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends Controller
{
    private AuthService $authService;
    protected FirebaseOAuth $firebase_oauth;

    public function __construct(AuthService $authService, FirebaseOAuth $firebase_oauth)
    {
        $this->authService = $authService;
        $this->firebase_oauth = $firebase_oauth;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->register($request->validated());
            if ($data['code'] == 422) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->login($request);
            if ($data['code'] == 401 || $data['code'] == 404 || $data['code'] == 403) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function logout(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->logout();
            if ($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function loginWithGoogle(Request $request)
    {
        $idToken = $request->input('id_token');
        $verifiedToken = $this->firebase_oauth->verifyToken($idToken);

        if (!$verifiedToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $firebaseUserId = $verifiedToken->claims()->get('sub');
        $email = $verifiedToken->claims()->get('email');
        $name = $verifiedToken->claims()->get('name') ?? 'No Name';

        // Split the name into parts
        $nameParts = explode(' ', $name);
        $firstName = array_shift($nameParts) ?? '';
        $lastName = implode(' ', $nameParts) ?? '';

        // Check if user exists by firebase_uid OR email
        $user = User::where('firebase_uid', $firebaseUserId)
                    ->orWhere('email', $email)
                    ->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'firebase_uid' => $firebaseUserId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
            ]);
            
            // Create moreDetail only for new users
            $user->moreDetail()->create();
        } else {
            // Update existing user with firebase_uid if missing
            if (!$user->firebase_uid) {
                $user->update(['firebase_uid' => $firebaseUserId]);
            }
            
            // Update user info if needed
            $user->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        }

        // Revoke existing tokens and create new one
        $user->tokens()->delete();
        $token = $user->createToken('authToken')->accessToken;

        $userData = $user->toArray();
        $userData['token'] = $token;
        
        $message = __('msg.login_success');
        $code = 200;

        return Response::success($userData, $message, $code);
    }
}
