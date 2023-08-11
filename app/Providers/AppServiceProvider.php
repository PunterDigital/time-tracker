<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Inertia::share([
            'userRoles' => function () {
                return auth()->user() && auth()->user()->currentTeam && auth()->user()->currentTeam->membership
                    ? auth()->user()->currentTeam->membership->role
                    : null;
            },
        ]);
    }
}
