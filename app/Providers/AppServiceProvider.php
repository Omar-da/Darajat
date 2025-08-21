<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\CoursePolicy;
use App\Policies\QuizPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        RateLimiter::for('resend-otp', function (Request $request) {
            if ($request->has('email')) {
                return Limit::perMinute(1)->by($request->input('email'));
            }
            return Limit::perMinute(2)->by($request->ip());
        });

        if (app()->environment('local')) {
            $this->app->booted(function () {
                $schedule = app(Schedule::class);
                $schedule->command('active:check')
                    // ->dailyAt('03:00') // Runs at 3 AM daily
                    ->everyMinute()
                    ->timezone('Asia/Damascus');
            });
        }

        if (str_contains(request()->getHttpHost(), 'ngrok-free.app')) {
        // Force Laravel to use the current request's scheme and host
        // This makes asset() URLs generate using https://your-url.ngrok-free.app
        URL::forceScheme('https');
        URL::forceRootUrl(request()->getSchemeAndHttpHost());
    }

    }
}


