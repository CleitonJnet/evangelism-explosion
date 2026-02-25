<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherForRegistrationsHeaderMeta(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows event name and ministry in registrations header', function (): void {
    $teacher = createTeacherForRegistrationsHeaderMeta();
    $church = Church::factory()->create();
    $ministry = Ministry::query()->create([
        'initials' => 'MTR',
        'name' => 'Ministerio Teste Registros',
        'logo' => null,
        'color' => '#0f172a',
        'description' => 'Descricao de teste',
    ]);
    $course = Course::factory()->create([
        'type' => 'Curso Registros',
        'name' => 'Evento Nome Registros',
        'ministry_id' => $ministry->id,
    ]);
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.registrations', $training))
        ->assertOk()
        ->assertSee('Curso Registros Evento Nome Registros')
        ->assertSee('Ministerio Teste Registros');
});
