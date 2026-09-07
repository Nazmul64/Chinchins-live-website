<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
        Paginator::useBootstrapFive();

        // Custom RBAC Blade Directives
        Blade::if('hasPermission', function ($permission) {
            $user = auth()->user();
            return $user && $user->hasPermission($permission);
        });

        Blade::if('hasRole', function ($role) {
            $user = auth()->user();
            return $user && $user->hasRole($role);
        });

        Blade::if('canAnyPermission', function ($permissions) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            if ($user->isSuperAdmin()) {
                return true;
            }
            return $user->hasAnyPermission((array) $permissions);
        });
    }
}
