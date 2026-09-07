<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Super Admin always allowed
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has any of the specified roles
        if (!empty($roles)) {
            $hasRole = false;
            foreach ($roles as $role) {
                $roleList = explode('|', $role);
                foreach ($roleList as $singleRole) {
                    if ($user->hasRole(trim($singleRole))) {
                        $hasRole = true;
                        break 2;
                    }
                }
            }

            if (!$hasRole) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Forbidden role.'], 403);
                }

                abort(403, 'Forbidden: You do not have the required role to access this area.');
            }
        }

        return $next($request);
    }
}
