<?php

use App\Models\Church;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Portals\PortalBaseCapabilityService;
use Tests\TestCase;

uses(TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

function portalBaseUser(array $roles, ?int $churchId = null): User
{
    $user = User::factory()->create(['church_id' => $churchId]);

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id);

    $user->roles()->syncWithoutDetaching($roleIds->all());

    return $user;
}

it('grants institutional base capabilities to directors and contextual fieldworkers only', function (): void {
    $church = Church::factory()->create();
    $director = portalBaseUser(['Director']);
    $fieldworker = portalBaseUser(['FieldWorker'], $church->id);
    $teacher = portalBaseUser(['Teacher'], $church->id);
    $service = app(PortalBaseCapabilityService::class);

    expect($service->baseSummary($director))->toMatchArray([
        'viewBaseOverview' => true,
        'manageBaseMembers' => true,
        'viewBaseParticipants' => true,
        'viewBaseInventory' => true,
    ])->and($service->baseSummary($fieldworker))->toMatchArray([
        'viewBaseOverview' => true,
        'manageBaseMembers' => true,
        'viewBaseParticipants' => true,
        'viewBaseInventory' => true,
    ])->and($service->baseSummary($teacher))->toMatchArray([
        'viewBaseOverview' => true,
        'manageBaseMembers' => false,
        'viewBaseParticipants' => false,
        'viewBaseInventory' => false,
    ]);
});

it('gives professors the ministerial event capabilities without granting institutional base ownership', function (): void {
    $church = Church::factory()->create();
    $teacher = portalBaseUser(['Teacher'], $church->id);
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $service = app(PortalBaseCapabilityService::class);

    expect($service->eventSummary($teacher, $training))->toMatchArray([
        'viewBaseOverview' => true,
        'manageBaseMembers' => false,
        'viewServedTrainings' => true,
        'manageTrainingRegistrations' => true,
        'manageEventSchedule' => true,
        'manageMentors' => true,
        'manageFacilitators' => true,
        'submitTeacherEventReport' => true,
        'submitChurchEventReport' => false,
        'viewEventMaterials' => true,
    ]);
});

it('keeps facilitators on hosted events in a local church lane with church report and materials access', function (): void {
    $church = Church::factory()->create();
    $facilitator = portalBaseUser(['Facilitator'], $church->id);
    $training = Training::factory()->create([
        'church_id' => $church->id,
        'teacher_id' => User::factory()->create()->id,
    ]);
    $service = app(PortalBaseCapabilityService::class);

    expect($service->eventSummary($facilitator, $training))->toMatchArray([
        'viewBaseOverview' => true,
        'manageTrainingRegistrations' => false,
        'manageEventSchedule' => false,
        'manageMentors' => false,
        'submitChurchEventReport' => true,
        'submitTeacherEventReport' => false,
        'viewBaseInventory' => true,
        'viewEventMaterials' => true,
    ]);
});

it('lets mentors keep their served training context without unlocking institutional base capabilities', function (): void {
    $mentor = portalBaseUser(['Mentor']);
    $training = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
    ]);
    $training->mentors()->attach($mentor->id, ['created_by' => User::factory()->create()->id]);
    $service = app(PortalBaseCapabilityService::class);

    expect($service->baseSummary($mentor))->toMatchArray([
        'viewBaseOverview' => false,
        'manageBaseMembers' => false,
        'viewBaseInventory' => false,
    ])->and($service->eventSummary($mentor, $training))->toMatchArray([
        'viewBaseOverview' => true,
        'viewServedTrainings' => true,
        'manageTrainingRegistrations' => false,
        'manageEventSchedule' => false,
        'submitChurchEventReport' => false,
        'submitTeacherEventReport' => false,
        'viewEventMaterials' => true,
    ]);
});
