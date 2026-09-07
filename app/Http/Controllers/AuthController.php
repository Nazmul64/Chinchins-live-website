<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login form (supports both /login and /admin/login).
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToRoleDashboard(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($request->input('email'));
        $password   = $request->input('password');
        $remember   = $request->boolean('remember');

        // Find user by email, phone, or account_id
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->orWhere('account_id', $identifier)
            ->first();

        // If user found, check lock status
        if ($user) {
            if ($user->is_locked || ($user->locked_until && Carbon::parse($user->locked_until)->isFuture())) {
                $lockedUntil = $user->locked_until ? Carbon::parse($user->locked_until)->diffForHumans() : 'further notice';
                LoginHistory::recordAttempt($user, false, 'Account locked until ' . $lockedUntil);

                return back()->withErrors([
                    'email' => "This account is currently locked ({$lockedUntil}). Reason: " . ($user->locked_reason ?? 'Security lock. Contact Super Admin.'),
                ])->onlyInput('email');
            }

            // Check if status is inactive or suspended
            if ($user->status === 'inactive' || $user->status === 'suspended') {
                LoginHistory::recordAttempt($user, false, "Account is {$user->status}");

                return back()->withErrors([
                    'email' => "Your administrative account is {$user->status}. Please contact the Super Admin.",
                ])->onlyInput('email');
            }
        }

        // Validate password
        if ($user && Hash::check($password, $user->password)) {
            // Check if user has administrative rights
            if (!$user->canAccessAdmin()) {
                LoginHistory::recordAttempt($user, false, 'Unauthorized: Non-admin user');
                return back()->withErrors([
                    'email' => 'Access denied: You do not have permission to access the Admin Panel.',
                ])->onlyInput('email');
            }

            // Successful Login
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until'          => null,
                'last_login_at'         => now(),
            ]);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Record audit logs
            LoginHistory::recordAttempt($user, true);
            ActivityLog::record(
                'login',
                'auth',
                "User {$user->name} logged into the Admin Panel",
                $user
            );

            return $this->redirectToRoleDashboard($user)
                ->with('success', 'Welcome back, ' . ($user->name ?? 'Admin') . '!');
        }

        // Failed Login Attempt
        if ($user) {
            $attempts = ($user->failed_login_attempts ?? 0) + 1;
            $updates = ['failed_login_attempts' => $attempts];

            // Lock out after 5 failed attempts for 15 minutes
            if ($attempts >= 5) {
                $updates['locked_until'] = now()->addMinutes(15);
                $updates['locked_reason'] = 'Too many failed login attempts (5+).';
            }
            $user->update($updates);

            LoginHistory::recordAttempt($user, false, 'Invalid password attempt #' . $attempts);
        } else {
            // Record failed attempt for non-existent user
            LoginHistory::recordAttempt(null, false, "Identifier '{$identifier}' not found");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record(
                'logout',
                'auth',
                "User {$user->name} logged out",
                $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out successfully.');
    }

    /**
     * Redirect to role-specific dashboard or default admin dashboard.
     */
    protected function redirectToRoleDashboard($user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isSubAdmin()) {
            return redirect()->route('admin.dashboard.sub_admin');
        }

        if ($user->isManager()) {
            return redirect()->route('admin.dashboard.manager');
        }

        if ($user->isEmployee()) {
            return redirect()->route('admin.dashboard.employee');
        }

        return redirect()->route('admin.dashboard');
    }
}
