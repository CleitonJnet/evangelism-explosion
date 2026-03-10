<?php

namespace App\Http\Controllers\System\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.app.roles.mentor.dashboard');
    }
}
