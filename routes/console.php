<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:restore {email=admin@gmail.com} {password=admin@gmail.com}', function (string $email, string $password) {
    $this->info("Restoring / creating admin account: {$email} ...");
    
    // Run Role Seeder
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RoleAndPermissionSeeder', '--force' => true]);
    
    // Run Admin User Seeder
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder', '--force' => true]);

    $user = \App\Models\User::withTrashed()->where('email', $email)->orWhere('email', 'like', "%{$email}")->first();
    if ($user) {
        if ($user->trashed()) {
            $user->restore();
        }
        $user->email = $email;
        $user->password = \Illuminate\Support\Facades\Hash::make($password);
        $user->is_active = true;
        $user->status = 'active';
        $user->is_locked = false;
        $user->locked_reason = null;
        $user->locked_until = null;
        $user->failed_login_attempts = 0;
        $user->save();
    }

    $this->info("Admin account successfully restored and ready!");
    $this->info("Email: {$email}");
    $this->info("Password: {$password}");
})->purpose('Restore or create primary super admin user account');
