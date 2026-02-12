<?php

namespace App\Http\Controllers\System\Student;

use App\Http\Controllers\Controller;
use App\Models\Training;

class TrainingController extends Controller
{
    public function index()
    {
        return view('pages.app.roles.student.trainings.index');
    }

    public function show($training_id)
    {
        return view('pages.app.roles.student.trainings.show', ['training' => Training::findOrFail($training_id)]);
    }
}
