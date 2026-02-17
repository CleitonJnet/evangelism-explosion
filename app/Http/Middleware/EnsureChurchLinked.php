<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChurchLinked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->church_id === null && $user->church_temp_id === null) {
            if (! $request->session()->has('church_modal_prompted')) {
                $request->session()->put('church_modal_open', true);
            }
        } else {
            $request->session()->forget(['church_modal_open', 'church_modal_prompted']);
        }

        return $next($request);
    }
}
