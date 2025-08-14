<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Mail\SendOTP;
use App\Mail\WelcomeUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OTPService
{
    public function sendOTP($user): void
    {
        $user->otp_code = rand(100000, 999999);
        $user->expire_at = now()->addMinutes(10);
        $user->otp_attempts_count = 0;
        $user->otp_locked_until = null;
        $user->save();
        Mail::to($user->email)->send(new SendOTP($user->otp_code));
    }

    public function resendOTP($request): array
    {
        $user = User::query()->where('email', $request['email'])->first();
        if ($user->otp_locked_until && now()->lessThan($user->otp_locked_until)) {
            return ['message' => __('msg.account_locked') . $user->otp_locked_until->diffForHumans() . '.', 'code' => 429];
        }
        $this->sendOTP($user);
        return ['user' => new UserResource($user), 'message' => __('msg.resend_otp_success'), 'code' => 200];
    }

    public function verifyOTP($request): array
    {
        $user = User::query()->where('email', $request['email'])->first();
        if ($user->otp_locked_until && now()->lessThan($user->otp_locked_until)) {
            return ['message' => __('msg.many_failed_otp_attempts') . $user->otp_locked_until->diffForHumans() . '.', 'code' => 429];
        } else if (!$user || $user->otp_code !== $request['otp_code'] || now()->greaterThan($user->expire_at)) {
            $user->otp_attempts_count++;
            if ($user->otp_attempts_count >= 5) {
                $user->otp_locked_until = now()->addMinutes(15);
                $user->otp_attempts_count = 0;
                $user->save();
                return ['message' => __('msg.account_locked'), 'code' => 429];
            }
            $user->save();
            return ['message' => __('msg.invalid_or_expired_otp'), 'code' => 422];
        }
        $user->otp_code = null;
        $user->expire_at = null;
        $user->otp_attempts_count = 0;
        $user->otp_locked_until = null;
        $user->markEmailAsVerified();
        $user->save();
        Mail::to($user)->send(new WelcomeUser($user));
        $token['token'] = $user->createToken('Personal Access Token')->accessToken;
        return ['token' => $token, 'message' => __('msg.email_verified'), 'code' => 200];
    }
}
