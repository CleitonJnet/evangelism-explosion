<?php

declare(strict_types=1);

use App\Livewire\Pages\App\Director\Course\View;
use App\Models\Course;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('manages course sections', function () {
    $course = Course::factory()->create();

    Livewire::test(View::class, ['course' => $course])
        ->set('sectionForm.name', 'Unidade 1')
        ->set('sectionForm.order', 1)
        ->set('sectionForm.duration', '1h')
        ->call('saveSection');

    $section = Section::query()->where('course_id', $course->id)->first();

    expect($section)->not->toBeNull();
    expect($section->name)->toBe('Unidade 1');

    Livewire::test(View::class, ['course' => $course])
        ->call('openEditSectionModal', $section->id)
        ->set('sectionForm.name', 'Unidade Atualizada')
        ->call('saveSection');

    expect($section->refresh()->name)->toBe('Unidade Atualizada');

    Livewire::test(View::class, ['course' => $course])
        ->call('openDeleteSectionModal', $section->id)
        ->call('confirmDeleteSection');

    expect(Section::query()->whereKey($section->id)->exists())->toBeFalse();
});

it('manages course teachers', function () {
    $course = Course::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $teacher = User::factory()->create();

    $teacher->roles()->attach($role->id);

    Livewire::test(View::class, ['course' => $course])
        ->set('teacherForm.user_id', $teacher->id)
        ->set('teacherForm.status', 1)
        ->call('saveTeacher');

    $teacherRelation = $course->teachers()->where('users.id', $teacher->id)->first();

    expect($teacherRelation)->not->toBeNull();
    expect((int) $teacherRelation->pivot->status)->toBe(1);

    Livewire::test(View::class, ['course' => $course])
        ->call('openEditTeacherModal', $teacher->id)
        ->set('teacherForm.status', 2)
        ->call('saveTeacher');

    $teacherRelation = $course->teachers()->where('users.id', $teacher->id)->first();

    expect((int) $teacherRelation->pivot->status)->toBe(2);

    Livewire::test(View::class, ['course' => $course])
        ->call('openDeleteTeacherModal', $teacher->id)
        ->call('confirmDeleteTeacher');

    expect($course->teachers()->where('users.id', $teacher->id)->exists())->toBeFalse();
});
