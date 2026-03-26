<?php

namespace App\Http\Middleware;

use App\Support\AccessProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RememberLastAccessProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();
        $role = $user ? AccessProfile::roleFromRouteName($routeName) : null;

        if ($role !== null && AccessProfile::userHasRole($user, $role)) {
            AccessProfile::remember($request, $user, $role);
        }

        return $next($request);
    }
}
