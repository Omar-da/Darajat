<?php

namespace App\Http\Controllers\App;

use App\Enums\RoleEnum;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Responses\Response;
use App\Services\Firebase\FirebaseOAuth;
use App\Services\User\AuthService;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    private AuthService $authService;
    // protected FirebaseOAuth $firebase;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        // $this->firebase = $firebase;
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
            $message  = $th->getMessage();
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
            $message  = $th->getMessage();
            return Response::error($message);
        }
    }

    public function loginWithGoogle(Request $request)
    {
        $idToken = $request->input('id_token');
        $verifiedToken = $this->firebase->verifyToken($idToken);

        if (!$verifiedToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $firebaseUserId = $verifiedToken->claims()->get('sub');
        $email = $verifiedToken->claims()->get('email');
        $name = $verifiedToken->claims()->get('name') ?? 'No Name';
        $picture = $verifiedToken->claims()->get('picture') ?? null;

        // Split the name into parts
        $nameParts = explode(' ', $name);

        // Extract first name (first part)
        $firstName = array_shift($nameParts) ?? '';

        // The rest is last name
        $lastName = implode(' ', $nameParts) ?? '';

        // Check if user exists in Laravel DB
        $user = User::firstOrCreate(
            ['firebase_uid' => $firebaseUserId],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'avatar' => $picture,
            ]
        );

        // Log the user in (Laravel session)
        auth()->login($user);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('authToken')->plainTextToken,
        ]);
    }
}
