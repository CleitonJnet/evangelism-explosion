<?php

use App\Livewire\Pages\App\Director\Course\View as CourseView;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForCourseTeacherToggle(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('toggles teacher status inline on the course details page', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 0,
        'name' => 'Clínica de Líderes',
    ]);

    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);
    $course->teachers()->attach($teacher->id, ['status' => 0]);

    $director = createDirectorForCourseTeacherToggle();

    Livewire::actingAs($director)
        ->test(CourseView::class, ['course' => $course])
        ->call('toggleTeacherStatus', $teacher->id, true);

    expect((int) $course->teachers()->where('users.id', $teacher->id)->first()->pivot->status)->toBe(1);
});
