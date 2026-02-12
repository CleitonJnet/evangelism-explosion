<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function create($church) 
    {
        return view("pages.app.roles.teacher.profiles.create", compact("church"));
    }

    public function show(string $church,string $profile)
    {
        return view("pages.app.roles.teacher.profiles.show", compact("church","profile"));
    }

    public function edit(string $church,string $profile)
    {
        return view("pages.app.roles.teacher.profiles.edit", compact("church","profile"));
    }
}
