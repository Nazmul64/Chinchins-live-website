<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Super admin bypasses all permission and status checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check account status
        if ($user->status === 'inactive' || $user->status === 'suspended' || $user->is_locked) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your administrative account has been deactivated or locked. Contact Super Admin.');
        }

        // Check if user has ANY of the specified permissions
        if (!empty($permissions)) {
            $hasAccess = false;
            foreach ($permissions as $perm) {
                // Support pipe-delimited or comma-delimited strings
                $permList = explode('|', $perm);
                foreach ($permList as $singlePerm) {
                    if ($user->hasPermission(trim($singlePerm))) {
                        $hasAccess = true;
                        break 2;
                    }
                }
            }

            if (!$hasAccess) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to access this resource.',
                    ], 403);
                }

                // Log unauthorized access attempt in audit logs
                \App\Models\ActivityLog::record(
                    'unauthorized_access',
                    'auth',
                    "User {$user->name} attempted unauthorized access to: " . $request->path(),
                    $user,
                    ['path' => $request->path(), 'required_permissions' => $permissions]
                );

                abort(403, 'Unauthorized Access: You do not have permission to access this page.');
            }
        }

        return $next($request);
    }
}
