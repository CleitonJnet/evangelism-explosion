<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\Training;
use App\Models\User;

function createTeacherUser(): User
{
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('forbids teachers from accessing other teachers ojt routes', function () {
    $owner = createTeacherUser();
    $otherTeacher = createTeacherUser();

    $training = Training::factory()->create([
        'teacher_id' => $owner->id,
    ]);

    $response = $this->actingAs($otherTeacher)
        ->post(route('app.teacher.trainings.ojt.teams.generate', [
            'training' => $training->id,
        ]));

    $response->assertForbidden();
});

it('allows teachers to access their own ojt routes', function () {
    $owner = createTeacherUser();

    $training = Training::factory()->create([
        'teacher_id' => $owner->id,
    ]);

    $response = $this->actingAs($owner)
        ->post(route('app.teacher.trainings.ojt.teams.generate', [
            'training' => $training->id,
        ]));

    $response->assertSuccessful()
        ->assertJson([
            'ok' => true,
        ]);
});
