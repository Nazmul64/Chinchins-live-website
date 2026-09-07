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

        // Auto-migrate and auto-seed RBAC database tables on production if not present
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('roles') || !\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder', '--force' => true]);
            }
        } catch (\Throwable $e) {}

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
