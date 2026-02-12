<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Ministry;

class MinistryController extends Controller
{
    public function index()
    {
        return view("pages.app.roles.director.ministry.index");
    }

    public function create()
    {
        return view("pages.app.roles.director.ministry.create");
    }

    public function show(string $id)
    {
        $ministry = Ministry::findOrFail($id);
        return view("pages.app.roles.director.ministry.show", ['ministry'=>$ministry]);
    }

    public function edit(string $id)
    {
        $ministry = Ministry::findOrFail($id);
        return view("pages.app.roles.director.ministry.edit", ['ministry'=>$ministry]);
    }
}
