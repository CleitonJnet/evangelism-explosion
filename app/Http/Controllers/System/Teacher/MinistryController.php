<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Ministry;

class MinistryController extends Controller
{
    public function index()
    {
        return view("pages.app.roles.teacher.ministry.index");
    }

    public function create()
    {
        return view("pages.app.roles.teacher.ministry.create");
    }

    public function show(string $id)
    {
        $ministry = Ministry::findOrFail($id);
        return view("pages.app.roles.teacher.ministry.show", ['ministry'=>$ministry]);
    }

    public function edit(string $id)
    {
        $ministry = Ministry::findOrFail($id);
        return view("pages.app.roles.teacher.ministry.edit", ['ministry'=>$ministry]);
    }
}
