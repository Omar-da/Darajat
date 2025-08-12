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
use App\Http\Controllers\App\StatisticController;
use App\Http\Controllers\App\TopicController;
use App\Http\Controllers\App\UserController;
use Illuminate\Support\Facades\Route;

// auth
Route::controller(AuthController::class)->middleware('set_language')->prefix('users')->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'logout');
    });
});

// profile
Route::controller(UserController::class)->middleware('set_language')->prefix('users')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::post('update-profile', 'updateProfile');
        Route::post('update-profile-image', 'updateProfileImage');
        Route::post('change-password', 'changePassword');
        Route::post('promote-student-to-teacher', 'promoteStudentToTeacher');
        Route::get('stripe-callback', 'stripeCallback')->name('users.stripe_callback');
        Route::delete('delete', 'destroy');
        Route::post('store-fcm-token', 'storeFCMToken');
    });
    Route::get('show-profile/{id}', 'showProfile');
    Route::get('get-certificates/{user_id}', 'get_certificates');
});


// reset password
Route::controller(ResetPasswordController::class)->middleware('set_language')->prefix('users/password')->group(function () {
    Route::post('email', 'forgotPassword');
    Route::post('code-check', 'checkCode');
    Route::post('reset', 'resetPassword');
});

// otp
Route::controller(OTPController::class)->middleware('set_language')->prefix('users/otp')->group(function () {
    Route::post('resend', 'resendOtp')->middleware('throttle:resend-otp');
    Route::post('verify', 'verifyOtp');
});

// quizzes
Route::controller(QuizController::class)->middleware(['auth:api', 'set_language'])->prefix('quizzes')->group(function () {
    Route::middleware('is_owner')->group(function () {
        Route::post('{episode_id}', 'store');
        Route::put('{quiz_id}', 'update');
        Route::delete('{quiz_id}', 'destroy');
    });
    Route::post('process-answer', 'processAnswer');
    Route::post('result/{quiz_id}', 'calculateQuizResult');
});

// categories
Route::controller(CategoryController::class)->middleware('set_language')->prefix('categories')->group(function () {
    Route::get('', 'index');
    Route::get('search/{title}', 'search');
});

// topics
Route::controller(TopicController::class)->middleware('set_language')->prefix('topics')->group(function () {
    Route::get('{category_id}', 'index');
    Route::get('search/{title}', 'search');
});

// courses
Route::controller(CourseController::class)->middleware('set_language')->prefix('courses')->group(function () {
    Route::get('', 'index');
    Route::post('load-more', 'loadMore');
    Route::get('category/{category_id}', 'getCoursesForCategory');
    Route::get('topic/{topic_id}', 'getCoursesForTopic');
    Route::get('language/{language_id}', 'getCoursesForLanguage');
    Route::get('search/{title}', 'search');
    Route::post('payment-process/{course}', 'paymentProcess')->name('courses.payment_process');
    Route::middleware('get_certificate')->group(function () {
        Route::post('obtain-certificate/{course}', 'obtainCertificate')->name('courses.obtain_certificate');
        Route::post('download-certificate/{course}', 'downloadCertificate')->name('courses.download_certificate');
    });
    Route::get('free', 'getFreeCourses');
    Route::get('paid', 'getPaidCourses');
    Route::get('student/{id}', 'showToStudent');
    Route::middleware('auth:api')->group(function () {
        Route::get('draft', 'getDraftCoursesToTeacher');
        Route::get('pending', 'getPendingCoursesToTeacher');
        Route::get('approved', 'getApprovedCoursesToTeacher');
        Route::get('rejected', 'getRejectedCoursesToTeacher');
        Route::post('', 'store')->middleware('is_teacher');
        Route::middleware('is_owner')->group(function () {
            Route::put('update-draft/{course_id}', 'updateDraftCourse');
            Route::patch('update-approved/{course_id}', 'updateApprovedCourse');
            Route::delete('{course_id}', 'destroy');
            Route::get('teacher/{course_id}', 'showToTeacher');
            Route::post('publish/{course_id}', 'publishCourse');
        });
        Route::get('with-arrangement/{topic_id}', 'getCoursesForTopicForTeacherWithArrangement');
        Route::post('evaluation/{course_id}', 'evaluation')->middleware('isSubscribed');
        Route::get('followed', 'getFollowedCoursesForStudent');
    });
});

// coupons
Route::controller(CouponController::class)->middleware(['auth:api', 'set_language'])->prefix('coupons')->group(function () {
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
Route::controller(EpisodeController::class)->middleware(['auth:api', 'set_language'])->prefix('episodes')->group(function () {
    Route::middleware('episode_protection')->group(function () {
        Route::post('add-like/{episode_id}', 'addLikeToEpisode');
        Route::delete('remove-like/{episode_id}', 'removeLikeFromEpisode');
        Route::post('finish/{episode_id}', 'finish_episode');
        Route::get('get_video/{episode_id}', 'get_video')->name('get_video');
        Route::get('get_poster/{episode_id}', 'get_poster')->name('get_poster');
        Route::get('show/student/{episode_id}', 'showToStudent');
        Route::get('download-file/{episode_id}', 'downloadFile');
    });
    Route::middleware('is_owner')->group(function () {
        Route::get('teacher/{course_id}', 'getToTeacher');
        Route::post('{course_id}', 'store');
        Route::put('update/{episode_id}', 'update');
        Route::get('show/teacher/{episode_id}', 'showToTeacher');
        Route::delete('{id}', 'destroy');
    });
    Route::get('student/{course_id}', 'getToStudent');
});

// comments
Route::controller(CommentController::class)->middleware(['auth:api', 'set_language'])->prefix('comments')->group(function () {
    Route::middleware('episode_protection')->group(function () {
        Route::get('{episode_id}', 'index');
        Route::post('load-more/{episode_id}', 'loadMore');
        Route::get('get-my-comments/{episode_id}', 'getMyComments');
    });
    Route::post('{episode_id}', 'store');
    Route::put('{id}', 'update');
    Route::delete('teacher/{id}', 'destroyForTeacher');
    Route::delete('student/{id}', 'destroyForStudent');
    Route::post('add-like/{id}', 'addLikeToComment');
    Route::delete('remove-like/{id}', 'removeLikeFromComment');
});

// replies
Route::controller(ReplyController::class)->middleware(['auth:api', 'set_language'])->prefix('replies')->group(function () {
    Route::get('{comment_id}', 'index');
    Route::post('{comment_id}', 'store');
    Route::put('{id}', 'update');
    Route::delete('teacher/{id}', 'destroyForTeacher');
    Route::delete('student/{id}', 'destroyForStudent');
    Route::post('add-like/{id}', 'addLikeToReply');
    Route::delete('remove-like/{id}', 'removeLikeFromReply');
});

// badges
Route::get('badges/get-my-badges', [BadgeController::class, 'index'])->middleware(['set_language', 'auth:api']);

// statistics
Route::get('statistics/get-my-statistics', [StatisticController::class, 'index'])->middleware(['set_language', 'auth:api']);

// constant values
Route::get('countries', [CountryController::class, 'index'])->middleware('set_language');

Route::get('languages', [LanguageController::class, 'index'])->middleware('set_language');

Route::get('skills', [SkillController::class, 'index'])->middleware('set_language');

Route::get('job_titles', [JobTitleController::class, 'index'])->middleware('set_language');

Route::controller(EnumController::class)->middleware('set_language')->group(function () {
    Route::get('levels', 'levels');
    Route::get('educations', 'educations');
});
