<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$registerAuthRoutes = function (): void {
    Route::controller(AuthController::class)->group(function (): void {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'resetPassword');
    });
};

$registerAuthRoutes();

Route::prefix('v1')->group(function () use ($registerAuthRoutes): void {
    $registerAuthRoutes();

    Route::apiResource('courses', CourseController::class);
    Route::apiResource('interests', InterestController::class);

    Route::middleware('auth:api')->group(function (): void {
        Route::get('/courses/favorites', [CourseController::class, 'favorites']);
        Route::get('/courses/matching-interests', [CourseController::class, 'matchingInterests']);

        Route::post('/courses/{course}/favorites', [FavoriteController::class, 'store']);
        Route::delete('/courses/{course}/favorites', [FavoriteController::class, 'destroy']);

        Route::post('/courses/{course}/join', [StudentController::class, 'joinCourse']);
        Route::post('/courses/{course}/checkout', [StudentController::class, 'checkoutCourse']);
        Route::delete('/courses/{course}/leave', [StudentController::class, 'leaveCourse']);

        Route::get('/teacher/courses/students', [TeacherController::class, 'enrolledStudents']);
        Route::get('/teacher/courses/groups', [TeacherController::class, 'courseGroups']);
        Route::get('/teacher/courses/groups/participants', [TeacherController::class, 'groupParticipants']);
    });

    Route::get('/payments/success', [StudentController::class, 'paymentSuccess'])->name('payments.success');
    Route::get('/payments/cancel', [StudentController::class, 'paymentCancel'])->name('payments.cancel');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
