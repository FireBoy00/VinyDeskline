<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDeskAssignment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // If user doesn't have a desk assigned
        if (is_null($user->desk_id)) {
            // Allow access to no-desk page, logout, and admin routes
            $allowedRoutes = [
                'no-desk',
                'logout',
                'admin.dashboard',
                'admin.arrangement',
                'admin.user-management',
                'admin.office-management',
                'admin.schedules',
                'admin.account',
            ];

            // Also allow all API routes for admins
            if ($user->is_admin && ($request->routeIs('admin.*') || $request->routeIs('api.*'))) {
                return $next($request);
            }

            // If trying to access a non-allowed route, redirect to no-desk page
            if (!$request->routeIs($allowedRoutes) && !$request->is('api/*')) {
                return redirect()->route('no-desk');
            }
        }

        return $next($request);
    }
}
