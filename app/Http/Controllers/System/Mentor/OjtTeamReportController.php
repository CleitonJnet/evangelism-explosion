<?php

namespace App\Http\Controllers\System\Mentor;

use App\Http\Controllers\Controller;
use App\Models\OjtTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OjtTeamReportController extends Controller
{
    public function create(OjtTeam $team): View
    {
        Gate::authorize('update', $team);

        return view('pages.app.roles.mentor.ojt.reports.form', [
            'team' => $team,
        ]);
    }

    public function edit(OjtTeam $team): View
    {
        Gate::authorize('update', $team);

        return view('pages.app.roles.mentor.ojt.reports.form', [
            'team' => $team,
        ]);
    }

    public function store(Request $request, OjtTeam $team): JsonResponse
    {
        Gate::authorize('update', $team);

        return response()->json([
            'ok' => true,
        ]);
    }

    public function update(Request $request, OjtTeam $team): JsonResponse
    {
        Gate::authorize('update', $team);

        return response()->json([
            'ok' => true,
        ]);
    }

    public function submit(Request $request, OjtTeam $team): JsonResponse
    {
        Gate::authorize('update', $team);

        return response()->json([
            'ok' => true,
        ]);
    }
}
