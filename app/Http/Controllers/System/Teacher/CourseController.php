<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function create($ministry) 
    {
        return view("pages.app.roles.teacher.course.create", compact("ministry"));
    }

    public function show(string $ministry,string $course)
    {
        return view("pages.app.roles.teacher.course.show", compact("ministry","course"));
    }

    public function edit(string $ministry,string $course)
    {
        return view("pages.app.roles.teacher.course.edit", compact("ministry","course"));
    }
}
