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
        ->set('sectionForm.devotional', 'Devocional 1')
        ->call('saveSection');

    $section = Section::query()->where('course_id', $course->id)->first();

    expect($section)->not->toBeNull();
    expect($section->name)->toBe('Unidade 1');

    Livewire::test(View::class, ['course' => $course])
        ->assertSee('Unidade 1')
        ->assertSee('Devocional 1');

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
        ->assertSee($teacher->name)
        ->assertSee($teacher->email);

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

it('paginates sections and teachers', function () {
    $course = Course::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);

    Section::factory()
        ->for($course)
        ->count(6)
        ->sequence(fn ($sequence) => [
            'name' => 'Unidade '.($sequence->index + 1),
            'order' => $sequence->index + 1,
        ])
        ->create();

    $teachers = collect(range(1, 6))
        ->map(fn (int $index) => User::factory()->create([
            'name' => 'Teacher '.$index,
            'email' => "teacher{$index}@example.com",
        ]));

    $teachers->each(function (User $teacher) use ($role, $course): void {
        $teacher->roles()->attach($role->id);
        $course->teachers()->attach($teacher->id, ['status' => 1]);
    });

    Livewire::test(View::class, ['course' => $course])
        ->assertSee('Unidade 1')
        ->assertSee('Unidade 5')
        ->assertDontSee('Unidade 6')
        ->assertSee('Teacher 1')
        ->assertSee('Teacher 5');

    Livewire::test(View::class, ['course' => $course])
        ->call('gotoPage', 2, 'sectionsPage')
        ->assertSee('Unidade 6')
        ->assertDontSee('Unidade 1');

    Livewire::test(View::class, ['course' => $course])
        ->call('gotoPage', 2, 'teachersPage')
        ->assertSee('Teacher 6');
});

it('reorders sections across pagination boundaries', function () {
    $course = Course::factory()->create();

    $sections = Section::factory()
        ->for($course)
        ->count(6)
        ->sequence(fn ($sequence) => [
            'order' => $sequence->index + 1,
        ])
        ->create()
        ->sortBy('order')
        ->values();

    $firstSection = $sections->first();

    Livewire::test(View::class, ['course' => $course])
        ->call('reorderSectionByIndex', $firstSection->id, 5, true);

    $orderedIds = Section::query()
        ->where('course_id', $course->id)
        ->orderBy('order')
        ->pluck('id')
        ->all();

    $expectedIds = $sections->slice(1)->pluck('id')->push($firstSection->id)->all();

    expect($orderedIds)->toBe($expectedIds);
});

it('moves a section to the previous page when dropped before the first row', function () {
    $course = Course::factory()->create();

    $sections = Section::factory()
        ->for($course)
        ->count(6)
        ->sequence(fn ($sequence) => [
            'order' => $sequence->index + 1,
        ])
        ->create()
        ->sortBy('order')
        ->values();

    $lastSection = $sections->last();

    Livewire::test(View::class, ['course' => $course])
        ->call('reorderSectionByIndex', $lastSection->id, 4, true);

    $orderedIds = Section::query()
        ->where('course_id', $course->id)
        ->orderBy('order')
        ->pluck('id')
        ->all();

    $expectedIds = [
        $sections[0]->id,
        $sections[1]->id,
        $sections[2]->id,
        $sections[3]->id,
        $lastSection->id,
        $sections[4]->id,
    ];

    expect($orderedIds)->toBe($expectedIds);
});
