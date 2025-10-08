<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessInactiveUser implements ShouldQueue
{
    use Queueable;

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

        $current_enthusiasm->pivot->update(['progress' => 0]);
        $user->moreDetail->num_of_inactive_days += 1;
        $user->moreDetail->save();

        if($user->moreDetail->num_of_inactive_days == 3)
            ProcessSendInactiveUserNotification::dispatch($user);
        
        $user->moreDetail->is_active_today = false;
        $user->moreDetail->save();

        DB::commit();
    }
}
