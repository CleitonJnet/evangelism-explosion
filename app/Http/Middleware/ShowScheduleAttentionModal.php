<?php

namespace App\Http\Middleware;

use App\Models\Training;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowScheduleAttentionModal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $training = $request->route('training');

        if (
            $training instanceof Training
            && $training->schedule_adjusted_at === null
            && $training->schedule_attention_shown_at === null
        ) {
            $now = Carbon::now();

            $training->forceFill([
                'schedule_attention_shown_at' => $now,
            ])->save();

            $request->session()->now('show_schedule_attention_modal', true);
        }

        return $next($request);
    }
}
