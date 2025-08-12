<?php

namespace App\Rules;

use App\Models\Course;
use App\Models\Episode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ConsecutiveEpisodeNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!Course::query()->find(request()->route('course_id'))) {
            return;
        }

        $last_episode = Episode::query()->where('course_id', request()->route('course_id'))->orderBy('episode_number', 'desc')->first();
        if(is_null($last_episode)) {
            if(request('episode_number') != 1) {
                $fail(__('msg.first_episode'));
            }
        } else if(request('episode_number') != $last_episode['episode_number'] + 1) {
            $n = $last_episode['episode_number'] + 1;
            $fail(__('msg.episodes_number_sequential') . $n);
        }
    }
}
