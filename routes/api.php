<?php


use App\Http\Controllers\App\BadgeController;
use App\Http\Controllers\App\CouponController;
use App\Http\Controllers\App\JobTitleController;
use App\Http\Controllers\App\AuthController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\CommentController;
use App\Http\Controllers\App\CountryController;
use App\Http\Controllers\App\CourseController;
use App\Http\Controllers\App\EnumController;
use App\Http\Controllers\App\EpisodeController;
use App\Http\Controllers\App\LanguageController;
use App\Http\Controllers\App\OTPController;
use App\Http\Controllers\App\QuizController;
use App\Http\Controllers\App\ReplyController;
use App\Http\Controllers\App\ResetPasswordController;
use App\Http\Controllers\App\SkillController;
use App\Http\Controllers\App\SpecialityController;
use App\Http\Controllers\App\StatisticController;
use App\Http\Controllers\App\TopicController;
use App\Http\Controllers\App\UniversityController;
use App\Http\Controllers\App\UserController;
use Illuminate\Support\Facades\Route;


Route::middleware('localization')->group(function() {

    // auth
    Route::controller(AuthController::class)->prefix('users')->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('login/google', 'loginWithGoogle');
        Route::get('logout', 'logout')->middleware('regular_or_socialite');
    });

    // profile
    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::middleware('regular_or_socialite')->group(function () {
            Route::post('update-profile', 'updateProfile');
            Route::post('update-profile-image', 'updateProfileImage');
            Route::post('change-password', 'changePassword');
            Route::post('promote-student-to-teacher', 'promoteStudentToTeacher');
            Route::get('stripe-callback', 'stripeCallback')->name('users.stripe_callback');
            Route::delete('delete', 'destroy');
            Route::post('store-fcm-token', 'storeFCMToken');    
        });
        Route::get('show-profile/{id}', 'showProfile');
    });


    // reset password
    Route::controller(ResetPasswordController::class)->prefix('users/password')->group(function () {
        Route::post('email', 'forgotPassword');
        Route::post('code-check', 'checkCode');
        Route::post('reset', 'resetPassword');
    });

    // otp
    Route::controller(OTPController::class)->prefix('users/otp')->group(function () {
        Route::post('resend', 'resendOtp')->middleware('throttle:resend-otp');
        Route::post('verify', 'verifyOtp');
    });

    // quizzes
    Route::controller(QuizController::class)->middleware('regular_or_socialite')->prefix('quizzes')->group(function () {
        Route::middleware('is_owner')->group(function () {
            Route::post('create/{episode_id}', 'store');
            Route::put('{quiz_id}', 'update');
            Route::delete('{quiz_id}', 'destroy');
        });
        Route::post('process-answer', 'processAnswer');
        Route::post('result/{quiz_id}', 'calculateQuizResult');
    });

    // categories
    Route::controller(CategoryController::class)->prefix('categories')->group(function () {
        Route::get('', 'index');
        Route::get('search/{title}', 'search');
    });

    // topics
    Route::controller(TopicController::class)->prefix('topics')->group(function () {
        Route::get('{category_id}', 'index');
        Route::get('search/{title}', 'search');
    });

// courses
Route::controller(CourseController::class)->middleware('localization')->prefix('courses')->group(function () {
    Route::get('', 'index');
    Route::post('load-more', 'loadMore');
    Route::get('category/{category_id}', 'getCoursesForCategory');
    Route::get('topic/{topic_id}', 'getCoursesForTopic');
    Route::get('language/{language_id}', 'getCoursesForLanguage');
    Route::get('search/{title}', 'search');
    Route::post('payment-process/{course}', 'paymentProcess')->name('courses.payment_process');
    Route::post('get-certificate/{course}', 'getCertificate')->name('courses.get_certificate')->middleware('get_certificate');
    Route::get('free', 'getFreeCourses');
    Route::get('paid', 'getPaidCourses');
    Route::get('student/{id}', 'showToStudent');
    Route::middleware('regular_or_socialite')->group(function () {
        Route::get('draft', 'getDraftCoursesToTeacher');
        Route::get('pending', 'getPendingCoursesToTeacher');
        Route::get('approved', 'getApprovedCoursesToTeacher');
        Route::get('rejected', 'getRejectedCoursesToTeacher');
        Route::get('deleted', 'getDeletedCoursesToTeacher');
        Route::post('', 'store')->middleware('is_teacher');
        Route::middleware('is_owner')->group(function () {
            Route::patch('update-approved/{course_id}', 'updateApprovedCourse');
            Route::patch('update-rejected/{course_id}', 'updateRejectedCourse');
            Route::patch('update-appending/{course_id}', 'updateAppendingCourse');
            Route::put('update-draft/{course_id}', 'updateDraftCourse');
            Route::delete('delete/{course_id}', 'destroy');
            Route::get('teacher/{course_id}', 'showToTeacher');
            Route::post('submit/{course_id}', 'submitCourse');
        });
        Route::patch('restore/{course_id}', 'restore');
        Route::get('with-arrangement/{topic_id}', 'getCoursesForTopicForTeacherWithArrangement')->middleware('is_teacher');;
        Route::patch('evaluation/{course_id}', 'evaluation')->middleware('is_subscribed');
        Route::get('followed', 'getFollowedCoursesForStudent');
    });
});

    // coupons
    Route::controller(CouponController::class)->middleware('regular_or_socialite')->prefix('coupons')->group(function () {
        Route::middleware('is_owner')->group(function () {
            Route::get('{course_id}', 'index');
            Route::post('{course_id}', 'store');
            Route::put('{coupon_id}', 'update');
            Route::get('show/{coupon_id}', 'show');
            Route::delete('{coupon_id}', 'destroy');
        });
        Route::post('apply/{course_id}', 'applyCoupon');
    });


    // episodes
    Route::controller(EpisodeController::class)->middleware('regular_or_socialite')->prefix('episodes')->group(function () {
        Route::middleware('episode_protection')->group(function () {
            Route::get('show/student/{episode_id}', 'showToStudent');
            Route::post('like/{episode_id}', 'like');
            Route::post('finish/{episode_id}', 'finishEpisode');
            Route::get('get_video/{episode_id}', 'get_video')->name('get_video');
            Route::get('get_poster/{episode_id}', 'get_poster')->name('get_poster');
            Route::get('download-file/{episode_id}', 'downloadFile');
        });
        Route::middleware('is_owner')->group(function () {
            Route::get('teacher/{course_id}', 'getToTeacher');
            Route::post('{course_id}', 'store');
            Route::put('update/{episode_id}', 'update');
            Route::get('show/teacher/{episode_id}', 'showToTeacher');
            Route::delete('{episode_id}', 'destroy');
        });
        Route::get('student/{course_id}', 'getToStudent')->middleware('is_subscribed');
    });

    // comments
    Route::controller(CommentController::class)->middleware('regular_or_socialite')->prefix('comments')->group(function () {
        Route::middleware('episode_protection')->group(function () {
            Route::get('{episode_id}', 'index');
            Route::post('load-more/{episode_id}', 'loadMore');
            Route::get('get-my-comments/{episode_id}', 'getMyComments');
        });
        Route::post('{episode_id}', 'store');
        Route::put('{id}', 'update');
        Route::delete('student/{id}', 'destroyForStudent');
        Route::post('like/{id}', 'like');
    });

    // replies
    Route::controller(ReplyController::class)->middleware('regular_or_socialite')->prefix('replies')->group(function () {
        Route::get('{comment_id}', 'index');
        Route::post('{comment_id}', 'store');
        Route::put('{id}', 'update');
        Route::delete('student/{id}', 'destroyForStudent');
        Route::post('like/{id}', 'like');
    });

    Route::controller(ReplyController::class)->middleware('regular_or_socialite')->group(function () {

        // badges
        Route::get('specialities', [SpecialityController::class, 'index']);

        // universities
        Route::get('universities', [UniversityController::class, 'index']);

        // badges
        Route::get('badges/get-my-badges', [BadgeController::class, 'index']);

        // statistics
        Route::get('statistics/get-my-statistics', [StatisticController::class, 'index']);

    });

    // constant values
    Route::get('countries', [CountryController::class, 'index']);

    Route::get('languages', [LanguageController::class, 'index']);

    Route::get('skills', [SkillController::class, 'index']);

    Route::get('job_titles', [JobTitleController::class, 'index']);

    Route::controller(EnumController::class)->group(function () {
        Route::get('levels', 'levels');
        Route::get('educations', 'educations');
    });

});