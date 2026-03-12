<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Support\TrainingAccess\TrainingCapabilityResolver;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

function assignRole(User $user, string $roleName): User
{
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('grants full training capabilities to directors', function (): void {
    $director = assignRole(User::factory()->create(), 'Director');
    $training = Training::factory()->create();
    $resolver = app(TrainingCapabilityResolver::class);

    expect($resolver->summary($director, $training))->toBe([
        'can_view' => true,
        'can_edit' => true,
        'can_delete' => true,
        'can_manage_schedule' => true,
        'can_view_stp_ojt' => true,
        'can_view_sensitive_data' => true,
        'can_view_finance' => true,
        'can_manage_mentors' => true,
        'can_see_discipleship' => true,
    ]);
});

it('grants teacher capabilities only for owned or assisted trainings', function (): void {
    $teacher = assignRole(User::factory()->create(), 'Teacher');
    $ownedTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);
    $assistedTraining = Training::factory()->create();
    $assistedTraining->assistantTeachers()->attach($teacher->id);
    $otherTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $resolver = app(TrainingCapabilityResolver::class);

    expect($resolver->summary($teacher, $ownedTraining))->toBe([
        'can_view' => true,
        'can_edit' => true,
        'can_delete' => true,
        'can_manage_schedule' => true,
        'can_view_stp_ojt' => true,
        'can_view_sensitive_data' => true,
        'can_view_finance' => true,
        'can_manage_mentors' => true,
        'can_see_discipleship' => true,
    ])->and($resolver->summary($teacher, $assistedTraining))->toBe([
        'can_view' => true,
        'can_edit' => true,
        'can_delete' => true,
        'can_manage_schedule' => true,
        'can_view_stp_ojt' => true,
        'can_view_sensitive_data' => true,
        'can_view_finance' => true,
        'can_manage_mentors' => true,
        'can_see_discipleship' => true,
    ])->and($resolver->summary($teacher, $otherTraining))->toBe([
        'can_view' => false,
        'can_edit' => false,
        'can_delete' => false,
        'can_manage_schedule' => false,
        'can_view_stp_ojt' => false,
        'can_view_sensitive_data' => false,
        'can_view_finance' => false,
        'can_manage_mentors' => false,
        'can_see_discipleship' => false,
    ]);
});

it('grants mentors read only access focused on stp and ojt for assigned trainings', function (): void {
    $mentor = assignRole(User::factory()->create(), 'Mentor');
    $training = Training::factory()->create();
    $training->mentors()->attach($mentor->id, ['created_by' => User::factory()->create()->id]);
    $resolver = app(TrainingCapabilityResolver::class);

    expect($resolver->summary($mentor, $training))->toBe([
        'can_view' => true,
        'can_edit' => false,
        'can_delete' => false,
        'can_manage_schedule' => false,
        'can_view_stp_ojt' => true,
        'can_view_sensitive_data' => false,
        'can_view_finance' => false,
        'can_manage_mentors' => false,
        'can_see_discipleship' => true,
    ]);
});

it('denies training capabilities to unrelated users', function (): void {
    $user = User::factory()->create();
    $training = Training::factory()->create();
    $resolver = app(TrainingCapabilityResolver::class);

    expect($resolver->summary($user, $training))->toBe([
        'can_view' => false,
        'can_edit' => false,
        'can_delete' => false,
        'can_manage_schedule' => false,
        'can_view_stp_ojt' => false,
        'can_view_sensitive_data' => false,
        'can_view_finance' => false,
        'can_manage_mentors' => false,
        'can_see_discipleship' => false,
    ]);
});

it('limits teacher context capabilities to assigned trainings even for teacher-directors', function (): void {
    $user = User::factory()->create();
    assignRole($user, 'Teacher');
    assignRole($user, 'Director');

    $ownedTraining = Training::factory()->create([
        'teacher_id' => $user->id,
    ]);
    $otherTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $resolver = app(TrainingCapabilityResolver::class);

    expect($resolver->summaryForTeacherContext($user, $ownedTraining))->toMatchArray([
        'can_view' => true,
        'can_edit' => true,
    ])->and($resolver->summaryForTeacherContext($user, $otherTraining))->toMatchArray([
        'can_view' => false,
        'can_edit' => false,
    ]);
});
