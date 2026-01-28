<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\OjtSession;
use App\Models\Training;
use App\Services\OJT\OjtSessionGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OjtSessionController extends Controller
{
    public function index(Training $training): View
    {
        $this->authorizeTraining($training);

        return view('pages.app.roles.teacher.trainings.ojt.sessions.index', [
            'training' => $training,
        ]);
    }

    public function create(Training $training): View
    {
        $this->authorizeTraining($training);

        return view('pages.app.roles.teacher.trainings.ojt.sessions.create', [
            'training' => $training,
        ]);
    }

    public function store(Request $request, Training $training): JsonResponse
    {
        $this->authorizeTraining($training);

        return response()->json([
            'ok' => true,
        ]);
    }

    public function generate(Training $training, OjtSessionGenerator $generator): JsonResponse
    {
        $this->authorizeTraining($training);

        $result = $generator->generate($training);

        return response()->json([
            'ok' => true,
            'created_count' => $result->created->count(),
            'canceled_count' => $result->canceled->count(),
        ]);
    }

    public function edit(Training $training, OjtSession $session): View
    {
        $this->authorizeTraining($training);
        $this->ensureSessionBelongsToTraining($training, $session);

        return view('pages.app.roles.teacher.trainings.ojt.sessions.edit', [
            'training' => $training,
            'session' => $session,
        ]);
    }

    public function update(Request $request, Training $training, OjtSession $session): JsonResponse
    {
        $this->authorizeTraining($training);
        $this->ensureSessionBelongsToTraining($training, $session);

        return response()->json([
            'ok' => true,
        ]);
    }

    public function destroy(Training $training, OjtSession $session): JsonResponse
    {
        $this->authorizeTraining($training);
        $this->ensureSessionBelongsToTraining($training, $session);

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

    private function ensureSessionBelongsToTraining(Training $training, OjtSession $session): void
    {
        if ($session->training_id !== $training->id) {
            abort(404);
        }
    }
}
