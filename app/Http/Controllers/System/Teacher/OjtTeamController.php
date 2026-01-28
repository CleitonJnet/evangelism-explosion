<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\OjtTeam;
use App\Models\Training;
use App\Services\OJT\OjtTeamGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OjtTeamController extends Controller
{
    public function index(Training $training): View
    {
        $this->authorizeTraining($training);

        return view('pages.app.roles.teacher.trainings.ojt.teams.index', [
            'training' => $training,
        ]);
    }

    public function generate(Request $request, Training $training, OjtTeamGenerator $generator): JsonResponse
    {
        $this->authorizeTraining($training);

        $result = $generator->generate($training);

        return response()->json([
            'ok' => true,
            'created_count' => $result->created->count(),
            'warnings' => $result->warnings,
        ]);
    }

    public function updateAssignments(Request $request, Training $training, OjtTeam $team): JsonResponse
    {
        $this->authorizeTraining($training);
        $this->ensureTeamBelongsToTraining($training, $team);

        return response()->json([
            'ok' => true,
        ]);
    }

    private function authorizeTraining(Training $training): void
    {
        $userId = Auth::id();

        if (! $userId) {
            abort(401);
        }

        if ($training->teacher_id !== $userId) {
            abort(403);
        }
    }

    private function ensureTeamBelongsToTraining(Training $training, OjtTeam $team): void
    {
        $team->loadMissing('session');

        if (! $team->session || $team->session->training_id !== $training->id) {
            abort(404);
        }
    }
}
