<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\View\View;

class OjtController extends Controller
{
    public function statistics(Training $training): View
    {
        $this->authorize('view', $training);

        return view('pages.app.roles.director.trainings.statistics', [
            'training' => $training,
        ]);
    }
}
