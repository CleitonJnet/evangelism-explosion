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
        $training->load([
            'course',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
        ]);

        return view('pages.app.roles.teacher.trainings.schedule', [
            'training' => $training,
            'eventDates' => $training->eventDates,
            'scheduleByDate' => $training->scheduleItems->groupBy(
                fn ($item) => $item->date?->format('Y-m-d')
            ),
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

        return redirect()->route('app.teacher.trainings.index');
    }
}
