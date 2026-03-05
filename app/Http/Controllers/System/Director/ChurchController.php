<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Church;
use Illuminate\View\View;

class ChurchController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Church::class);

        return view('pages.app.roles.director.churches.index');
    }

    public function create(): View
    {
        $this->authorize('create', Church::class);

        return view('pages.app.roles.director.churches.create');
    }

    public function show(Church $church): View
    {
        $this->authorize('view', $church);

        return view('pages.app.roles.director.churches.show', ['church' => $church]);
    }

    public function edit(Church $church): View
    {
        $this->authorize('update', $church);

        return view('pages.app.roles.director.churches.edit', ['church' => $church]);
    }

    public function make_host(): View
    {
        return view('pages.app.roles.director.churches.make_host');
    }

    public function view_host(Church $church): View
    {
        $this->authorize('view', $church);

        return view('pages.app.roles.director.churches.view_host', ['church' => $church]);
    }

    public function edit_host(Church $church): View
    {
        $this->authorize('update', $church);

        return view('pages.app.roles.director.churches.edit_host', ['church' => $church]);
    }
}
