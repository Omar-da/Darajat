<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Traits\BadgeTrait;
use Illuminate\Support\Facades\DB;

class ProcessActiveUser implements ShouldQueue
{
    use Queueable;
    use BadgeTrait;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120];
    
    /**
     * Create a new job instance.
     */
    public function __construct(public $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        $user = $this->user;
        $current_enthusiasm = $user->statistics()->where('title->en', 'Current Enthusiasm')->first();
        $max_enthusiasm = $user->statistics()->where('title->en', 'Max Enthusiasm')->first();

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

        $user->moreDetail->is_active_today = false;
        $user->moreDetail->save();

        DB::commit();
    }
}
