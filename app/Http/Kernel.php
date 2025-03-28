<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // middleware lain yang sudah ada...
        'activity.tracker' => \App\Http\Middleware\ActivityTrackerMiddleware::class,
        'login' => \App\Http\Middleware\LoginMiddleware::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'autologout' => \App\Http\Middleware\AutoLogoutMiddleware::class,
    ];
} 