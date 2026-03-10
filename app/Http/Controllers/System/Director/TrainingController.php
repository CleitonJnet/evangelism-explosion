<?php

namespace App\Http\Controllers\System\Director;

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

    public function create(): View
    {
        return view('pages.app.roles.director.trainings.create');
    }

    public function show(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.director.trainings.show', ['training' => $training]);
    }

    public function edit(Training $training): View
    {
        $this->authorize('update', $training);

        return view('pages.app.roles.director.trainings.edit', ['training' => $training]);
    }

    public function schedule(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.director.trainings.schedule', [
            'training' => $training,
        ]);
    }

    public function registrations(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.director.trainings.registrations', [
            'training' => $training,
        ]);
    }

    public function testimony(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.director.trainings.testimony', [
            'training' => $training,
        ]);
    }

    public function updateTestimony(UpdateTrainingTestimonyRequest $request, Training $training): RedirectResponse
    {
        $this->authorize('update', $training);

        $sanitizedNotes = TestimonySanitizer::sanitize($request->validated('notes'));

        $training->update([
            'notes' => $sanitizedNotes,
        ]);

        return redirect()
            ->route('app.director.training.testimony', $training)
            ->with('success', __('Relato salvo com sucesso.'));
    }

    public function destroy(Training $training): RedirectResponse
    {
        $this->authorize('delete', $training);

        $training->delete();

        return redirect()->route('app.director.training.index');
    }

    private function renderIndex(TrainingIndexFilterRequest $request, string $status): View
    {
        return view('pages.app.roles.director.trainings.index', [
            ...$this->trainingIndexService->buildIndexData($request->user(), $status, $request->filterTerm(), [
                'planning' => 'app.director.training.planning',
                'scheduled' => 'app.director.training.scheduled',
                'canceled' => 'app.director.training.canceled',
                'completed' => 'app.director.training.completed',
            ]),
            'filter' => $request->filterTerm(),
        ]);
    }
}
