<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OjtStatsController extends Controller
{
    public function summary(Training $training): View
    {
        $this->authorizeTraining($training);

        return view('pages.app.roles.teacher.trainings.ojt.stats.summary', [
            'training' => $training,
        ]);
    }

    public function publicReport(Training $training): View
    {
        $this->authorizeTraining($training);

        return view('pages.app.roles.teacher.trainings.ojt.stats.public-report', [
            'training' => $training,
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
}
