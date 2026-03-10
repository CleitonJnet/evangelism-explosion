<?php

namespace App\Http\Controllers\System\Mentor;

use App\Http\Controllers\Controller;
use App\Models\StpSession;
use Illuminate\View\View;

class OjtSessionController extends Controller
{
    public function index(): View
    {
        return view('pages.app.roles.mentor.ojt.sessions.index');
    }

    public function show(StpSession $session): View
    {
        $this->authorize('view', $session);

        return view('pages.app.roles.mentor.ojt.sessions.show', [
            'session' => $session,
        ]);
    }
}
