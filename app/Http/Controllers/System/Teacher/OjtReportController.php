<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\OjtReport;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OjtReportController extends Controller
{
    public function index(Training $training): View
    {
        $this->authorizeTraining($training);

        return view('pages.app.roles.teacher.trainings.ojt.reports.index', [
            'training' => $training,
        ]);
    }

    public function show(Training $training, OjtReport $report): View
    {
        $this->authorizeTraining($training);
        $this->ensureReportBelongsToTraining($training, $report);

        return view('pages.app.roles.teacher.trainings.ojt.reports.show', [
            'training' => $training,
            'report' => $report,
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

    private function ensureReportBelongsToTraining(Training $training, OjtReport $report): void
    {
        $report->loadMissing('team.session');

        if (! $report->team || ! $report->team->session || $report->team->session->training_id !== $training->id) {
            abort(404);
        }
    }
}
