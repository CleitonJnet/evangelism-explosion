<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function create($church) 
    {
        return view("pages.app.roles.director.profiles.create", compact("church"));
    }

    public function show(string $church,string $profile)
    {
        return view("pages.app.roles.director.profiles.show", compact("church","profile"));
    }

    public function edit(string $church,string $profile)
    {
        return view("pages.app.roles.director.profiles.edit", compact("church","profile"));
    }
}
