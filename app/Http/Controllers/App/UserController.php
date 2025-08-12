<?php

namespace App\Http\Controllers\App;

use App\Enums\RoleEnum;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ProfileImageRequest;
use App\Http\Requests\User\ProfileRequest;
use App\Models\User;
use App\Responses\Response;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Request;
use Stripe\OAuth;
use Stripe\Stripe;
use Throwable;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateProfile(ProfileRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->updateProfile($request->validated());
            return Response::success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }
    public function updateProfileImage(ProfileImageRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->updateProfileImage($request->validated());
            return Response::success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->changePassword($request->validated());
            if($data['code'] == 401) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function showProfile($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->showProfile($id);
            if($data['code'] == 404) {
                return Response::error($data['message'], $data['code']);
            }
            return Response::success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error( $message);
        }
    }

    public function promoteStudentToTeacher()
    {
        // $data = [];
        // try
        // {
        //     $data = $this->userService->promoteStudentToTeacher();
        //     if($data['code'] == 409)
        //         return Response::error($data['message'], $data['code']);
        //     return Response::success([], $data['message']);
        // } catch (Throwable $th) {
        //     $message = $th->getMessage();
        //     return Response::error($message);
        // }
        // $user = auth('api')->user();
        // if($user['role'] === RoleEnum::TEACHER) {
        //     return ['message' => 'You are already a Teacher!', 'code' => 409];
        // }
        // $user->update([
        //     'role' => 'teacher'
        // ]);

        $clientId = config('services.stripe.connect');
        $redirectUri = urlencode(route('users.stripe_callback'));

        return redirect("https://connect.stripe.com/oauth/authorize?response_type=code&client_id={$clientId}&scope=read_write&redirect_uri={$redirectUri}");
    }

    public function stripeCallback(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret'));

        try {
                $response = OAuth::token([
                    'grant_type' => 'authorization_code',
                    'code' => $request->code,
                ]);

                // Save Stripe account ID to teacher
                auth('api')->user()->update(['stripe_connect_id' => $response->stripe_user_id]);

                return response()->json([
                    'message' => 'Promotion succeeded'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Connection failed'
                ]);
            }

    }

    public function destroy(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->delete();
            return Response::success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::error($message);
        }
    }

    public function storeFCMToken(Request $request)
    {
        $user = User::find($request->user_id);
        $user->update(['fcm_token' => $request->device_token]);
        return response()->json(['success' => true]);
    }
}
