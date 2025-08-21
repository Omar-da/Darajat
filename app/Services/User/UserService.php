<?php

namespace App\Services\User;

use App\Enums\LevelEnum;
use App\Enums\RoleEnum;
use App\Http\Resources\User\UserResource;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function updateProfile($request): array
    {
        $user = User::query()->find(auth('api')->id());
        $moreDetail = $user->moreDetail;

        $user->update([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
        ]);

        if(!is_null($request['speciality']) && !is_numeric($request['speciality'])) {
            $speciality = Speciality::query()->create([
                'name' => $request['speciality'],
            ]);
            $request['speciality'] = $speciality->id;
        }

        $moreDetail->update([
            'country_id' => $request['country_id'],
            'job_title_id' => $request['job_title_id'] ?? null,
            'linked_in_url' => $request['linked_in_url'] ?? null,
            'education' => $request['education'],
            'university_id' => $request['university_id'] ?? null,
            'speciality_id' => $request['speciality'] ?? null,
            'work_experience' => $request['work_experience'] ?? null,
        ]);

        $syncData = [];
        $check = 0;

        foreach ($request['languages'] as $languageInfo) {
            $languageId = $languageInfo['language_id'] ?? null;
            $level = $languageInfo['level'] ?? null;

            if(!in_array($level, LevelEnum::values())) {
                foreach (LevelEnum::values() as $value) {
                    if($level == LevelEnum::from($value)->label()) {
                        $level = $value;
                        break;
                    }
                }
            }

            if ($languageId !== null && $level !== null) {
                if($level == 'mother_tongue') {
                    $check = 1;
                }
                $syncData[$languageId] = ['level' => $level];
            }
        }

        if(!$check) {
            return ['message' => __('msg.mother_language'), 'code' => 422];
        }

        $moreDetail->languages()->sync($syncData);

        $syncSkillData = [];
        if (isset($request['skills']) && is_array($request['skills'])) {
            foreach ($request['skills'] as $skillInfo) {
                $skillId = $skillInfo['skill_id'] ?? null;
                if ($skillId !== null) {
                    $syncSkillData[$skillId] = [];
                }
            }
        }

        $moreDetail->skills()->sync($syncSkillData);
        return ['user' => new UserResource($user), 'message' => __('msg.profile_updated'), 'code' => 200];
    }

    public function updateProfileImage($request): array
    {
        $user = User::query()->find(auth('api')->id());
        if(!is_null($user->profile_image_url)) {
            Storage::delete("profiles/$user->profile_image_url");
        }
        if (!empty($request['profile_image_url'])) {
            $path = basename($request['profile_image_url']->store('profiles'));
            $user->update([
                'profile_image_url' => $path,
            ]);
        } else {
            $user->update([
                'profile_image_url' => null,
            ]);
        }
        return ['user' => new UserResource($user), 'message' => __('msg.profile_image_updated')];
    }

    public function changePassword($request): array
    {
        $user = User::query()->find(auth('api')->id());
        if(!Hash::check($request['old_password'], $user['password'])) {
            $message = __('msg.old_password');
            $code = 401;
        } else {
            $user->update([
                'password' => Hash::make($request['new_password']),
            ]);
            $message = __('msg.change_password');
            $code = 200;
        }
        return ['user' => new UserResource($user), 'message' => $message, 'code' => $code];
    }

    public function showProfile($id): array
    {
        $user = User::query()->find($id);
        if(!$user) {
            $message = __('msg.user_not_found');
            $code = 404;
        } else {
            $message = __('msg.user_retrieved');
            $code = 200;
        }
        return ['user' => new UserResource($user), 'message' => $message, 'code' => $code];
    }

    public function promoteStudentToTeacher()
    {

    }

    public function delete(): array
    {
        $user = User::query()->find(auth('api')->id());
        $user->delete();
        return ['user' => [], 'message' => __('msg.user_deleted')];
    }

}
