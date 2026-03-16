<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Support\TrainingAccess\TrainingVisibilityScope;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

function assignTrainingRole(User $user, string $roleName): User
{
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('shows all trainings to directors', function (): void {
    $director = assignTrainingRole(User::factory()->create(), 'Director');
    $visibleIds = Training::factory()->count(3)->create()->pluck('id')->all();

    $scopedIds = app(TrainingVisibilityScope::class)
        ->apply(Training::query()->orderBy('id'), $director)
        ->pluck('id')
        ->all();

    expect($scopedIds)->toBe($visibleIds);
});

it('shows teachers only their owned and assisted trainings', function (): void {
    $teacher = assignTrainingRole(User::factory()->create(), 'Teacher');
    $ownedTraining = Training::factory()->create(['teacher_id' => $teacher->id]);
    $assistedTraining = Training::factory()->create();
    $assistedTraining->assistantTeachers()->attach($teacher->id);
    $otherTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);

    $scopedIds = app(TrainingVisibilityScope::class)
        ->apply(Training::query()->orderBy('id'), $teacher)
        ->pluck('id')
        ->all();

    expect($scopedIds)->toBe([
        $ownedTraining->id,
        $assistedTraining->id,
    ])->not->toContain($otherTraining->id);
});

it('shows mentors only trainings where they are attached', function (): void {
    $mentor = assignTrainingRole(User::factory()->create(), 'Mentor');
    $visibleTraining = Training::factory()->create();
    $hiddenTraining = Training::factory()->create();
    $visibleTraining->mentors()->attach($mentor->id, ['created_by' => User::factory()->create()->id]);

    $scopedIds = app(TrainingVisibilityScope::class)
        ->apply(Training::query()->orderBy('id'), $mentor)
        ->pluck('id')
        ->all();

    expect($scopedIds)->toBe([$visibleTraining->id])
        ->not->toContain($hiddenTraining->id);
});

it('combines teacher and mentor visibility when the user has both roles', function (): void {
    $user = User::factory()->create();
    assignTrainingRole($user, 'Teacher');
    assignTrainingRole($user, 'Mentor');

    $ownedTraining = Training::factory()->create(['teacher_id' => $user->id]);
    $mentoredTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $otherTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $mentoredTraining->mentors()->attach($user->id, ['created_by' => User::factory()->create()->id]);

    $scopedIds = app(TrainingVisibilityScope::class)
        ->apply(Training::query()->orderBy('id'), $user)
        ->pluck('id')
        ->all();

    expect($scopedIds)->toBe([
        $ownedTraining->id,
        $mentoredTraining->id,
    ])->not->toContain($otherTraining->id);
});

it('limits teacher context to owned and assisted trainings even for teacher-directors', function (): void {
    $user = User::factory()->create();
    assignTrainingRole($user, 'Teacher');
    assignTrainingRole($user, 'Director');

    $ownedTraining = Training::factory()->create(['teacher_id' => $user->id]);
    $otherTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);

    $scopedIds = app(TrainingVisibilityScope::class)
        ->apply(Training::query()->orderBy('id'), $user, 'teacher')
        ->pluck('id')
        ->all();

    expect($scopedIds)->toBe([$ownedTraining->id])
        ->not->toContain($otherTraining->id);
});

it('limits serving context to explicit assignments even for teacher-directors', function (): void {
    $user = User::factory()->create();
    assignTrainingRole($user, 'Teacher');
    assignTrainingRole($user, 'Director');
    assignTrainingRole($user, 'Mentor');

    $ownedTraining = Training::factory()->create(['teacher_id' => $user->id]);
    $mentoredTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $hiddenTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $mentoredTraining->mentors()->attach($user->id, ['created_by' => User::factory()->create()->id]);

    $scopedIds = app(TrainingVisibilityScope::class)
        ->apply(Training::query()->orderBy('id'), $user, 'serving')
        ->pluck('id')
        ->all();

    expect($scopedIds)->toBe([
        $ownedTraining->id,
        $mentoredTraining->id,
    ])->not->toContain($hiddenTraining->id);
});
