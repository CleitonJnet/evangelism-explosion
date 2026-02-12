<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureChurchLinked;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;

function createTeacherUserForOjtPages(): User
{
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('loads teacher ojt pages', function () {
    $teacher = createTeacherUserForOjtPages();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->withoutMiddleware([EnsureChurchLinked::class]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.ojt.sessions.index', $training))
        ->assertSuccessful()
        ->assertSee('OJT Sessions');

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.ojt.teams.index', $training))
        ->assertSuccessful()
        ->assertSee('OJT Teams');

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.ojt.reports.index', $training))
        ->assertSuccessful()
        ->assertSee('OJT Reports');

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.ojt.stats.summary', $training))
        ->assertSuccessful()
        ->assertSee('OJT Statistics');

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.ojt.stats.public-report', $training))
        ->assertSuccessful()
        ->assertSee('OJT Statistics');
});
