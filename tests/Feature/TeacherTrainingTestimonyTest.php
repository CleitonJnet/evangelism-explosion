<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Training\TestimonySanitizer;

function createTeacherForTrainingTestimony(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows testimony page for the training owner teacher', function () {
    $teacher = createTeacherForTrainingTestimony();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.testimony', $training))
        ->assertOk()
        ->assertSee('Relato do Evento');
});

it('forbids another teacher from accessing testimony page and update action', function () {
    $ownerTeacher = createTeacherForTrainingTestimony();
    $otherTeacher = createTeacherForTrainingTestimony();
    $training = Training::factory()->create([
        'teacher_id' => $ownerTeacher->id,
    ]);

    $this->actingAs($otherTeacher)
        ->get(route('app.teacher.trainings.testimony', $training))
        ->assertForbidden();

    $this->actingAs($otherTeacher)
        ->put(route('app.teacher.trainings.testimony.update', $training), [
            'notes' => '<p>Relato</p>',
        ])
        ->assertForbidden();
});

it('saves sanitized testimony in training notes', function () {
    $teacher = createTeacherForTrainingTestimony();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'notes' => null,
    ]);

    $response = $this->actingAs($teacher)
        ->put(route('app.teacher.trainings.testimony.update', $training), [
            'notes' => '<p style="text-align:center;color:#1d4ed8" onclick="alert(1)">Texto <strong>formatado</strong> <script>alert(1)</script></p>',
        ]);

    $response
        ->assertRedirect(route('app.teacher.trainings.testimony', $training))
        ->assertSessionHas('status');

    $training->refresh();

    expect($training->notes)->not->toBeNull();
    expect($training->notes)->toContain('<strong>formatado</strong>');
    expect($training->notes)->toContain('text-align:center');
    expect($training->notes)->toContain('color:#1d4ed8');
    expect($training->notes)->not->toContain('onclick=');
    expect($training->notes)->not->toContain('<script');
});

it('validates testimony character limit by plain text length', function () {
    $teacher = createTeacherForTrainingTestimony();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $tooLongText = str_repeat('a', TestimonySanitizer::MAX_CHARACTERS + 1);

    $this->actingAs($teacher)
        ->from(route('app.teacher.trainings.testimony', $training))
        ->put(route('app.teacher.trainings.testimony.update', $training), [
            'notes' => "<p>{$tooLongText}</p>",
        ])
        ->assertRedirect(route('app.teacher.trainings.testimony', $training))
        ->assertSessionHasErrors('notes');
});

it('shows testimony button and hides mentors button on training details page', function () {
    $teacher = createTeacherForTrainingTestimony();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertOk()
        ->assertSee('Relato')
        ->assertDontSee('Gerenciador de mentores');
});
