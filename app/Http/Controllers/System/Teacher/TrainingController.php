<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingIndexFilterRequest;
use App\Http\Requests\UpdateTrainingTestimonyRequest;
use App\Models\Training;
use App\Services\Training\TestimonySanitizer;
use App\Services\Training\TrainingIndexService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __construct(private TrainingIndexService $trainingIndexService) {}

    public function index(TrainingIndexFilterRequest $request): View
    {
        return $this->renderIndex($request, 'scheduled');
    }

    public function indexByStatus(TrainingIndexFilterRequest $request, string $status): View
    {
        return $this->renderIndex($request, $status);
    }

    public function create()
    {
        return view('pages.app.roles.teacher.trainings.create');
    }

    public function show(Training $training): View
    {
        $this->authorize('viewTeacherContext', $training);

        return view('pages.app.roles.teacher.trainings.show', ['training' => $training]);
    }

    public function schedule(Training $training): View
    {
        $this->authorize('viewTeacherContext', $training);

        return view('pages.app.roles.teacher.trainings.schedule', [
            'training' => $training,
        ]);
    }

    public function registrations(Training $training): View
    {
        $this->authorize('viewTeacherContext', $training);

        return view('pages.app.roles.teacher.trainings.registrations', [
            'training' => $training,
        ]);
    }

    public function testimony(Training $training): View
    {
        $this->authorize('viewTeacherContext', $training);

        return view('pages.app.roles.teacher.trainings.testimony', [
            'training' => $training,
        ]);
    }

    public function updateTestimony(UpdateTrainingTestimonyRequest $request, Training $training): RedirectResponse
    {
        $this->authorize('updateTeacherContext', $training);

        $sanitizedNotes = TestimonySanitizer::sanitize($request->validated('notes'));

        $training->update([
            'notes' => $sanitizedNotes,
        ]);

        return redirect()
            ->route('app.teacher.trainings.testimony', $training)
            ->with('success', __('Relato salvo com sucesso.'));
    }

    public function destroy(Training $training): RedirectResponse
    {
        $this->authorize('deleteTeacherContext', $training);

        $training->delete();

        return redirect()->route('app.teacher.trainings.index');
    }

    private function renderIndex(TrainingIndexFilterRequest $request, string $status): View
    {
        return view('pages.app.roles.teacher.trainings.index', [
            ...$this->trainingIndexService->buildIndexData($request->user(), $status, $request->filterTerm(), [
                'planning' => 'app.teacher.trainings.planning',
                'scheduled' => 'app.teacher.trainings.scheduled',
                'canceled' => 'app.teacher.trainings.canceled',
                'completed' => 'app.teacher.trainings.completed',
            ], 'teacher'),
            'filter' => $request->filterTerm(),
        ]);
    }
}
