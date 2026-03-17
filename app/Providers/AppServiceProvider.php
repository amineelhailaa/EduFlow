<?php

namespace App\Providers;

use App\Contracts\AuthRepositoryInterface;
use App\Contracts\CourseRepositoryInterface;
use App\Contracts\StudentRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Repositories\CourseRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
