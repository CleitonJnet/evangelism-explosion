<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\View\View;

class OjtController extends Controller
{
    public function statistics(Training $training): View
    {
        $this->authorize('viewTeacherContext', $training);

        return view('pages.app.roles.teacher.trainings.statistics', [
            'training' => $training,
        ]);
    }
}
