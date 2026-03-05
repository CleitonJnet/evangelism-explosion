<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\View\View;

class StpApproachController extends Controller
{
    public function board(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.director.trainings.stp-approaches', [
            'training' => $training,
        ]);
    }
}
