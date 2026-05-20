<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (method_exists($user, 'hasRole')) {
            $roles = (array) config('tyro-dashboard.admin_roles', ['admin', 'super-admin']);
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }
}
