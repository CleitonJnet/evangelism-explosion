<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs(
            'logout',
            'force-password-change.show',
            'force-password-change.update',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
        )) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(423, 'Password change required.');
        }

        return redirect()->route('force-password-change.show');
    }
}
