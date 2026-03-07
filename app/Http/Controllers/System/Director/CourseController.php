<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Ministry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseController extends Controller
{
    public function create(Ministry $ministry)
    {
        return view('pages.app.roles.director.course.create', compact('ministry'));
    }

    public function show(Ministry $ministry, Course $course)
    {
        $this->ensureCourseBelongsToMinistry($ministry, $course);

        return view('pages.app.roles.director.course.show', compact('ministry', 'course'));
    }

    public function edit(Ministry $ministry, Course $course)
    {
        $this->ensureCourseBelongsToMinistry($ministry, $course);

        return view('pages.app.roles.director.course.edit', compact('ministry', 'course'));
    }

    public function sections(Ministry $ministry, Course $course)
    {
        $this->ensureCourseBelongsToMinistry($ministry, $course);

        return view('pages.app.roles.director.course.sections', compact('ministry', 'course'));
    }

    private function ensureCourseBelongsToMinistry(Ministry $ministry, Course $course): void
    {
        if ((int) $course->ministry_id !== (int) $ministry->id) {
            throw new NotFoundHttpException;
        }
    }
}
