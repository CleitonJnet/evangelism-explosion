<?php

namespace App\Livewire\Pages\App\Director\Course;

use App\Models\Course;
use Livewire\Component;

class View extends Component
{
    public $course;

    public function mount(Course $course) {
        $this->course = $course;
    }

    public function render()
    {
        return view('livewire.pages.app.director.course.view', [
            'teachers' => $this->course->teachers()->paginate(10),
        ]);
    }
}
