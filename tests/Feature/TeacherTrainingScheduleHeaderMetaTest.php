<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;

function createTeacherForScheduleHeaderMeta(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows event name and ministry on the right side of schedule header', function (): void {
    $teacher = createTeacherForScheduleHeaderMeta();
    $church = Church::factory()->create();
    $ministry = Ministry::query()->create([
        'initials' => 'MTC',
        'name' => 'Ministerio Teste Cabecalho',
        'logo' => null,
        'color' => '#0f172a',
        'description' => 'Descricao de teste',
    ]);
    $course = Course::factory()->create([
        'type' => 'Curso Header',
        'name' => 'Evento Nome Exclusivo',
        'ministry_id' => $ministry->id,
    ]);
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertSee('Curso Header Evento Nome Exclusivo')
        ->assertSee('Ministerio Teste Cabecalho')
        ->assertSee($church->name);
});
