<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\View\View;

class StpApproachController extends Controller
{
    public function board(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.teacher.trainings.stp-approaches', [
            'training' => $training,
        ]);
    }
}
