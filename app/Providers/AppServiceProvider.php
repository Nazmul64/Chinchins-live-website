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
            if (!$user) {
                return false;
            }
            if ($user->isSuperAdmin()) {
                return true;
            }
            try {
                return $user->hasPermission($permission);
            } catch (\Throwable $e) {
                return false;
            }
        });

        Blade::if('hasRole', function ($role) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            if ($user->isSuperAdmin()) {
                return true;
            }
            try {
                return $user->hasRole($role);
            } catch (\Throwable $e) {
                return false;
            }
        });

        Blade::if('canAnyPermission', function ($permissions) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            if ($user->isSuperAdmin()) {
                return true;
            }
            try {
                return $user->hasAnyPermission((array) $permissions);
            } catch (\Throwable $e) {
                return false;
            }
        });
    }
}
