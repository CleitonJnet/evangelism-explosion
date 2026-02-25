<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherForStpHeaderMeta(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows event title and ministry in statistics page header', function (): void {
    $teacher = createTeacherForStpHeaderMeta();
    $church = Church::factory()->create();
    $ministry = Ministry::query()->create([
        'initials' => 'MTS',
        'name' => 'Ministerio Teste STP',
        'logo' => null,
        'color' => '#0f172a',
        'description' => 'Descricao de teste',
    ]);
    $course = Course::factory()->create([
        'type' => 'Curso STP',
        'name' => 'Evento STP Header',
        'ministry_id' => $ministry->id,
    ]);
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.statistics', $training))
        ->assertOk()
        ->assertSee('Curso STP Evento STP Header')
        ->assertSee('Ministerio Teste STP')
        ->assertSee($church->name);
});

it('shows event title and ministry in stp approaches page header', function (): void {
    $teacher = createTeacherForStpHeaderMeta();
    $church = Church::factory()->create();
    $ministry = Ministry::query()->create([
        'initials' => 'MTV',
        'name' => 'Ministerio Teste Visitas',
        'logo' => null,
        'color' => '#0f172a',
        'description' => 'Descricao de teste',
    ]);
    $course = Course::factory()->create([
        'type' => 'Curso Visitas',
        'name' => 'Evento Visitas Header',
        'ministry_id' => $ministry->id,
    ]);
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.stp.approaches', $training))
        ->assertOk()
        ->assertSee('Curso Visitas Evento Visitas Header')
        ->assertSee('Ministerio Teste Visitas')
        ->assertSee($church->name);
});
