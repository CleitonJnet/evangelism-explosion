<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function testimonials(): View
    {
        return view('pages.app.roles.director.website.testimonials');
    }
}
