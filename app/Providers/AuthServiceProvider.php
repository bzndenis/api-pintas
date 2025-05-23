<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('Authorization')) {
                $token = str_replace('Bearer ', '', $request->header('Authorization'));
                return User::where('remember_token', $token)->first();
            }
        });

        // Definisikan gate untuk admin
        Gate::define('admin', function ($user) {
            return in_array($user->role, ['admin', 'super_admin']);
        });
        
        // Definisikan gate untuk guru
        Gate::define('guru', function ($user) {
            return $user->role === 'guru';
        });
    }
}
