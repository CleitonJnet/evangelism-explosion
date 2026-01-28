<?php

namespace App\Http\Controllers\System\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OjtController extends Controller
{
    public function index(): View
    {
        return view('pages.app.roles.mentor.ojt.index');
    }
}
