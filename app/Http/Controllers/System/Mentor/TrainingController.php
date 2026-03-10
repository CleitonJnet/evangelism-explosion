<?php

namespace App\Http\Controllers\System\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        return view('pages.app.roles.mentor.trainings.index');
    }

    public function show(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.mentor.trainings.show', [
            'training' => $training,
        ]);
    }

    public function ojt(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.mentor.trainings.ojt', [
            'training' => $training,
        ]);
    }
}
