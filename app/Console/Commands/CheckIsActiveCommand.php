<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Jobs\ProcessActiveUser;
use App\Jobs\ProcessInactiveUser;
use App\Jobs\ProcessSendInactiveUserNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class CheckIsActiveCommand extends Command
{
    protected $signature = 'active:check';

    protected $description = 'check if user is active daily';

    public function __construct()   
    {
        parent::__construct();
    }

    public function handle(): void
    {
        foreach(User::all() as $user)
        {
            if($user->role === RoleEnum::ADMIN)
                continue;

            if($user->moreDetail->is_active_today)
                ProcessActiveUser::dispatch($user)->onQueue('active_user');
            else
                ProcessInactiveUser::dispatch($user);
        }
    }
}
