<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminStatusMiddleware
{
    /**
     * Handle an incoming request.
     * Ensure the authenticated user has active administrative privileges and is not locked/suspended.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user has an admin role or is super-admin
        if (!$user->canAccessAdmin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account does not have administrative access or has been deactivated.',
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Access denied. Your administrative account is inactive or not authorized.');
        }

        // Check account lock
        if ($user->is_locked || ($user->locked_until && $user->locked_until->isFuture())) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Account is locked. Reason: ' . ($user->locked_reason ?? 'Security lockout. Please contact Super Admin.'));
        }

        return $next($request);
    }
}
