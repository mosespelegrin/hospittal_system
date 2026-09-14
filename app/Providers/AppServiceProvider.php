<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // Admins bypass every permission check below instead of needing
        // every permission explicitly assigned to the admin role.
        Gate::before(fn ($user, string $ability) => $user->hasRole('admin') ? true : null);
    }
}
