<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Training;

class TrainingController extends Controller
{
    public function index()
    {
        return view("pages.app.roles.director.trainings.index");
    }

    public function create()
    {
        return view("pages.app.roles.director.trainings.create");
    }

    public function show(string $id)
    {
        $training = Training::findOrFail($id);
        return view("pages.app.roles.director.trainings.show", ['training'=>$training]);
    }

    public function edit(string $id)
    {
        $training = Training::findOrFail($id);
        return view("pages.app.roles.director.trainings.edit",['training'=>$training]);
    }
}
