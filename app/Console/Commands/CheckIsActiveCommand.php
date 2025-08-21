<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\Firebase\FCMService;
use App\Traits\BadgeTrait;
use Illuminate\Console\Command;

class CheckIsActiveCommand extends Command
{
    use BadgeTrait;
    protected $signature = 'active:check';

    protected $description = 'check if user is active daily';

    public function __construct(public FCMService $fcmService)    // Injection the fcm service
    {
        parent::__construct();
    }

    public function handle(): void
    {

        foreach(User::all() as $user)
        {
            if($user->role === RoleEnum::ADMIN)
                continue;
            $current_enthusiasm = $user->statistics()->where('title->en', 'Current Enthusiasm')->first();
            $max_enthusiasm = $user->statistics()->where('title->en', 'Max Enthusiasm')->first();

            if($user->moreDetail->is_active_today)
            {
                $current_enthusiasm->pivot->increment('progress');
                $user->moreDetail->num_of_inactive_days = 0;
                $user->moreDetail->save();

                if($current_enthusiasm->pivot->progress > $max_enthusiasm->pivot->progress)
                    $max_enthusiasm->pivot->update(['progress' => $current_enthusiasm->pivot->progress]);

                switch ($current_enthusiasm->pivot->progress) {
                    case 20:
                        $user->badges()->attach(1);
                        $this->bronzeBadge();
                        $user->statistics()->where('title->en', 'Num Of Bronze Badges')->first()->pivot->increment('progress');
                        $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                        break;
                    case 50:
                        $user->badges()->attach(2);
                        $this->silverBadge();
                        $user->statistics()->where('title->en', 'Num Of Silver Badges')->first()->pivot->increment('progress');
                        $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                        break;
                    case 100:
                        $user->badges()->attach(3);
                        $this->goldBadge();
                        $user->statistics()->where('title->en', 'Num Of Gold Badges')->first()->pivot->increment('progress');
                        $user->statistics()->where('title->en', 'Num Of Badges')->first()->pivot->increment('progress');
                        break;
                }

            }
            else
            {
                $current_enthusiasm->pivot->update(['progress' => 0]);
                $user->moreDetail->num_of_inactive_days += 1;
                $user->moreDetail->save();

                if($user->moreDetail->num_of_inactive_days == 3)
                {
                    $this->fcmService->sendNotification(
                        $user->fcm_token,
                        __('msg.steps'),
                        __('msg.three_days')
                    );
                }
            }

            $user->moreDetail->is_active_today = false;
            $user->moreDetail->save();
        }
    }
}
