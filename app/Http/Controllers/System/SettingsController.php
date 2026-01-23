<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function profile() {
        return view("pages.app.settings.profile");
    }
}
