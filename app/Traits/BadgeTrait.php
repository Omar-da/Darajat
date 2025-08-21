<?php

namespace App\Traits;

use App\Services\Firebase\FCMService;

trait BadgeTrait
{
    public function __construct(public FCMService $fcmService)    // Injection the fcm service
    {
        $this->fcmService = $fcmService;
    }

    public function bronzeBadge(): void
    {
        $this->fcmService->sendNotification(
            auth('api')->user()->fcm_token,
            __('msg.badge_bronze'),
            __('msg.congratulations') . __('msg.badge_bronze'),
        );
    }

    public function silverBadge(): void
    {
        $this->fcmService->sendNotification(
            auth('api')->user()->fcm_token,
            __('msg.badge_silver'),
            __('msg.congratulations') . __('msg.badge_silver'),
        );
    }

    public function goldBadge(): void
    {
        $this->fcmService->sendNotification(
            auth('api')->user()->fcm_token,
            __('msg.badge_gold'),
            __('msg.congratulations') . __('msg.badge_gold'),
        );
    }


    public function checkStatistic($course): void
    {
        $user = auth('api')->user();

        // Increment number of completed courses
        $s_total = $user->statistics()->where('title->en', 'Total Completed Courses')->first();
        $s_total->pivot->increment('progress');

        // Get the topics of completed courses
        $topics = $user->topics();

        // Attach topic in completed courses
        $topics->sync($course->topic_id);

        // Increment number of completed courses for specific topic
        $topics->where('topic_id', $course->topic_id)->first()->pivot->increment('progress');

        // Fetch on the maximum progress in topics
        $max_progress = $topics->orderBy('progress', 'DESC')->first()->pivot->progress;

        // Update progress statistic
        $s_max = $user->statistics()->where('title->en', 'Max Of Total Completed Courses In One Topic')->first();
        $s_max->pivot->update(['progress' => $max_progress]);

        switch ($s_total->pivot->progress) {
            case 5:
                $user->badges()->attach(4);
                $this->bronzeBadge();
                $user->statistics()->where('title->en', 'Num Of Bronze Badges')->first()->pivot->increment('progress');
                $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                break;
            case 15:
                $user->badges()->attach(5);
                $this->silverBadge();
                $user->statistics()->where('title->en', 'Num Of Silver Badges')->first()->pivot->increment('progress');
                $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                break;
            case 25:
                $user->badges()->attach(6);
                $this->goldBadge();
                $user->statistics()->where('title->en', 'Num Of Gold Badges')->first()->pivot->increment('progress');
                $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                break;
        }

        switch ($s_max->pivot->progress) {
            case 3:
                $user->badges()->attach(7);
                $this->bronzeBadge();
                $user->statistics()->where('title->en', 'Num Of Bronze Badges')->first()->pivot->increment('progress');
                $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                break;
            case 5:
                $user->badges()->attach(8);
                $this->silverBadge();
                $user->statistics()->where('title->en', 'Num Of Silver Badges')->first()->pivot->increment('progress');
                $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                break;
            case 10:
                $user->badges()->attach(9);
                $this->goldBadge();
                $user->statistics()->where('title->en', 'Num Of Gold Badges')->first()->pivot->increment('progress');
                $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                break;
        }
    }
}
