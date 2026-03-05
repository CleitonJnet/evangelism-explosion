<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTrainingTestimonyRequest;
use App\Models\Training;
use App\Services\Training\TestimonySanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        return view('pages.app.roles.director.trainings.index', [
            'statusKey' => 'scheduled',
        ]);
    }

    public function indexByStatus(string $status): View
    {
        return view('pages.app.roles.director.trainings.index', [
            'statusKey' => $status,
        ]);
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
}
