<?php

namespace App\Services\User;

use App\Enums\LevelEnum;
use App\Http\Resources\User\UserResource;
use App\Models\Country;
use App\Models\Language;
use App\Models\MoreDetail;
use App\Models\PlatformStatistics;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function register($request): array
    {
        $user = User::query()->create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        if (!$user) {
            $message = __('msg.registration_failed');
            $code = 422;
        } else {
            $this->otpService->sendOTP($user);
            $moreDetail = MoreDetail::query()->create([
                'user_id' => $user->id,
                'country_id' => $request['country_id'],
            ]);
            if(MoreDetail::where('country_id', $moreDetail->country_id)->doesntExist())
                PlatformStatistics::incrementStat('num_of_countries');
            $moreDetail->languages()->attach(Language::query()->findOrfail($request['language_id']),
                [
                    'language_id' => $request['language_id'],
                    'more_detail_id' => $moreDetail->id,
                    'level' => LevelEnum::MOTHER_TONGUE
                ]);
            PlatformStatistics::incrementStat('num_of_students');
            $message = __('msg.registration_success');
            $code = 202;
        }
        return ['user' => new UserResource($user), 'message' => $message, 'code' => $code];
    }

    public function login($request): array
    {
        $user = User::query()->where('email', $request['email'])->first();

        if (!is_null($user)) {
            if (!Auth::attempt($request->only(['email', 'password']))) {
                $message = __('msg.not_match');
                $code = 401;
            } else if (is_null($user->email_verified_at)) {
                return ['message' => __('msg.email_not_verified'), 'code' => 403];
            } else {
                $token = $user->createToken('Personal Access Token')->accessToken;
                $user = (new UserResource($user))->toArray(request());
                $user['token'] = $token;
                $message = __('msg.login_success');
                $code = 200;
            }
        } else {
            $message = __('msg.user_not_found');;
            $code = 404;
        }
        return ['user' => $user, 'message' => $message, 'code' => $code];
    }

    public function logout(): array
    {
        $user = auth('api')->user();
        $user->token()->revoke();
        $message = __('msg.logout_success');
        $code = 200;
        return ['user' => [], 'message' => $message, 'code' => $code];
    }
}
