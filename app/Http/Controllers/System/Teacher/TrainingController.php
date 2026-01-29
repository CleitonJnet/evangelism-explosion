<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Training;
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

    public function show(string $id)
    {
        $training = Training::findOrFail($id);

        return view('pages.app.roles.teacher.trainings.show', ['training' => $training]);
    }

    public function schedule(Training $training): View
    {
        return view('pages.app.roles.teacher.trainings.schedule', [
            'training' => $training,
        ]);
    }

    public function edit(string $id)
    {
        $training = Training::findOrFail($id);

        return view('pages.app.roles.teacher.trainings.edit', ['training' => $training]);
    }

    public function destroy(Training $training): RedirectResponse
    {
        $training->delete();

        return redirect()->route('app.teacher.training.index');
    }
}
