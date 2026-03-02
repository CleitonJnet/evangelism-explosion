<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Church;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChurchController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Church::class);

        return view('pages.app.roles.teacher.churches.index');
    }

    public function create(): View
    {
        $this->authorize('create', Church::class);

        return view('pages.app.roles.teacher.churches.create');
    }

    public function show(Church $church): View
    {
        $this->authorize('view', $church);

        return view('pages.app.roles.teacher.churches.show', ['church' => $church]);
    }

    public function edit(Church $church): View
    {
        $this->authorize('update', $church);

        return view('pages.app.roles.teacher.churches.edit', ['church' => $church]);
    }

    public function destroy(Church $church): RedirectResponse
    {
        $this->authorize('delete', $church);

        $church->delete();

        return redirect()
            ->route('app.teacher.churches.index')
            ->with('success', __('Igreja removida com sucesso.'));
    }

    public function make_host(): View
    {
        return view('pages.app.roles.teacher.churches.make_host');
    }

    public function view_host(Church $church): View
    {
        $this->authorize('view', $church);

        return view('pages.app.roles.teacher.churches.view_host', ['church' => $church]);
    }

    public function edit_host(Church $church): View
    {
        $this->authorize('update', $church);

        return view('pages.app.roles.teacher.churches.edit_host', ['church' => $church]);
    }
}
