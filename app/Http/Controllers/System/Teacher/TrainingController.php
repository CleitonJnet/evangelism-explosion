<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTrainingTestimonyRequest;
use App\Models\Training;
use App\Services\Training\TestimonySanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index()
    {
        return view('pages.app.roles.teacher.trainings.index', [
            'statusKey' => 'scheduled',
        ]);
    }

    public function indexByStatus(string $status)
    {
        return view('pages.app.roles.teacher.trainings.index', [
            'statusKey' => $status,
        ]);
    }

    public function create()
    {
        return view('pages.app.roles.teacher.trainings.create');
    }

    public function show(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.teacher.trainings.show', ['training' => $training]);
    }

    public function schedule(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.teacher.trainings.schedule', [
            'training' => $training,
        ]);
    }

    public function registrations(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.teacher.trainings.registrations', [
            'training' => $training,
        ]);
    }

    public function testimony(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.teacher.trainings.testimony', [
            'training' => $training,
        ]);
    }

    public function updateTestimony(UpdateTrainingTestimonyRequest $request, Training $training): RedirectResponse
    {
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
        $this->authorize('delete', $training);

        $training->delete();

        return redirect()->route('app.teacher.trainings.index');
    }
}
