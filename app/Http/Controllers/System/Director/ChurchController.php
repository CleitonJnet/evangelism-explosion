<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Church;

class ChurchController extends Controller
{
    public function index()
    {
        return view('pages.app.roles.director.churches.index');
    }

    public function create()
    {
        return view('pages.app.roles.director.churches.create');
    }

    public function show(string $church)
    {
        $church = Church::findOrFail($church);

        return view('pages.app.roles.director.churches.show', ['church' => $church]);
    }

    public function edit(string $church)
    {
        $church = Church::findOrFail($church);

        return view('pages.app.roles.director.churches.edit', ['church' => $church]);
    }

    public function make_host()
    {
        return view('pages.app.roles.director.churches.make_host');
    }

    public function view_host($church)
    {
        return view('pages.app.roles.director.churches.view_host', ['church' => $church]);
    }

    public function edit_host($church)
    {
        return view('pages.app.roles.director.churches.edit_host', ['church' => $church]);
    }
}
