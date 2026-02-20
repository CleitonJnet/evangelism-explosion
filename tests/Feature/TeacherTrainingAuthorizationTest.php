<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherWithRole(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

dataset('teacher training protected routes', [
    'show' => fn (Training $training): string => route('app.teacher.trainings.show', $training),
    'schedule' => fn (Training $training): string => route('app.teacher.trainings.schedule', $training),
    'registrations' => fn (Training $training): string => route('app.teacher.trainings.registrations', $training),
    'statistics' => fn (Training $training): string => route('app.teacher.trainings.statistics', $training),
]);

it('forbids a teacher from accessing another teacher training', function (callable $routeResolver) {
    $ownerTeacher = createTeacherWithRole();
    $otherTeacher = createTeacherWithRole();
    $training = Training::factory()->create([
        'teacher_id' => $ownerTeacher->id,
    ]);

    $response = $this->actingAs($otherTeacher)->get($routeResolver($training));

    $response->assertForbidden();
})->with('teacher training protected routes');

it('allows the training owner teacher to access their training', function (callable $routeResolver) {
    $ownerTeacher = createTeacherWithRole();
    $training = Training::factory()->create([
        'teacher_id' => $ownerTeacher->id,
    ]);

    $response = $this->actingAs($ownerTeacher)->get($routeResolver($training));

    $response->assertOk();
})->with('teacher training protected routes');
