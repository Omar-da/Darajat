<?php

namespace App\Jobs;

use App\Services\Firebase\FCMService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSendInactiveUserNotification implements ShouldQueue
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
    public function handle(FCMService $fcmService): void
    {
        $fcmService->sendNotification(
                $this->user->fcm_token,
                __('msg.steps'),
                __('msg.three_days')
            );
    }
}
