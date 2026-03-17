<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::apiResource('courses', CourseController::class);

    Route::middleware('auth:api')->post('/courses/{course}/join', [StudentController::class, 'joinCourse']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
