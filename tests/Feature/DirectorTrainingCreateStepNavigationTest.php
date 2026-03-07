<?php

use App\Livewire\Pages\App\Director\Training\Create;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForTrainingCreate(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

function createTeacherForDirectorTrainingCreate(string $name, string $email): User
{
    $teacher = User::factory()->create([
        'name' => $name,
        'email' => $email,
    ]);

    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows available courses for the director on the create flow', function (): void {
    $director = createDirectorForTrainingCreate();
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Clínica de Liderança',
    ]);

    $this->actingAs($director);

    Livewire::test(Create::class)
        ->assertSee('Clínica de Liderança');
});

it('shows the save button only on the sixth step for the director flow', function (): void {
    $director = createDirectorForTrainingCreate();

    $this->actingAs($director);

    Livewire::test(Create::class)
        ->assertSee('Próximo')
        ->assertDontSee('Salvar evento')
        ->set('step', 6)
        ->assertSee('Salvar evento');
});

it('filters teacher candidates by selected course and saves the selected principal teacher', function (): void {
    $director = createDirectorForTrainingCreate();
    $eligibleTeacher = createTeacherForDirectorTrainingCreate('Ana Professora', 'ana@example.test');
    $inactiveTeacher = createTeacherForDirectorTrainingCreate('Bianca Inativa', 'bianca@example.test');
    $outsiderTeacher = createTeacherForDirectorTrainingCreate('Carlos Externo', 'carlos@example.test');
    $church = Church::factory()->create();
    $course = Course::factory()->create([
        'execution' => 0,
        'price' => '120,00',
    ]);

    $course->teachers()->syncWithoutDetaching([
        $eligibleTeacher->id => ['status' => 1],
        $inactiveTeacher->id => ['status' => 0],
    ]);

    $component = Livewire::actingAs($director)
        ->test(Create::class)
        ->set('course_id', $course->id)
        ->set('teacherSearch', 'Ana')
        ->assertSee('Ana Professora')
        ->assertDontSee('Bianca Inativa')
        ->assertDontSee('Carlos Externo')
        ->assertSet('teacher_id', $eligibleTeacher->id)
        ->set('eventDates', [
            ['date' => '2026-04-10', 'start_time' => '08:00', 'end_time' => '12:00'],
        ])
        ->set('church_id', $church->id)
        ->call('submit')
        ->assertRedirect(route('app.director.training.show', ['training' => 1]));

    $training = Training::query()->first();

    expect($training)->not->toBeNull()
        ->and($training?->teacher_id)->toBe($eligibleTeacher->id)
        ->and($training?->course_id)->toBe($course->id)
        ->and($training?->church_id)->toBe($church->id);
});
