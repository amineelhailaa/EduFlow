<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::apiResource('courses', CourseController::class);
    Route::apiResource('interests', InterestController::class);

    Route::middleware('auth:api')->post('/courses/{course}/join', [StudentController::class, 'joinCourse']);
    Route::middleware('auth:api')->post('/courses/{course}/checkout', [StudentController::class, 'checkoutCourse']);
    Route::middleware('auth:api')->delete('/courses/{course}/leave', [StudentController::class, 'leaveCourse']);
    Route::middleware('auth:api')->get('/teacher/courses/students', [TeacherController::class, 'enrolledStudents']);
    Route::get('/payments/success', [StudentController::class, 'paymentSuccess'])->name('payments.success');
    Route::get('/payments/cancel', [StudentController::class, 'paymentCancel'])->name('payments.cancel');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
