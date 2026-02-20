<?php

use App\Enums\StpApproachStatus;
use App\Models\Church;
use App\Models\Course;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use App\Services\Stp\StpBoardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('moves approach and normalizes positions in source and destination containers', function (): void {
    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create();
    $mentor = User::factory()->create();

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $team = StpTeam::factory()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'position' => 0,
    ]);

    $queueA = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'status' => StpApproachStatus::Planned->value,
        'position' => 4,
        'created_by_user_id' => $teacher->id,
    ]);
    $queueMove = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'status' => StpApproachStatus::Planned->value,
        'position' => 9,
        'created_by_user_id' => $teacher->id,
    ]);
    $queueC = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'status' => StpApproachStatus::Planned->value,
        'position' => 20,
        'created_by_user_id' => $teacher->id,
    ]);

    $teamA = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'status' => StpApproachStatus::Assigned->value,
        'position' => 8,
        'created_by_user_id' => $teacher->id,
    ]);
    $teamB = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'status' => StpApproachStatus::Assigned->value,
        'position' => 17,
        'created_by_user_id' => $teacher->id,
    ]);

    $this->actingAs($teacher);

    app(StpBoardService::class)->moveApproach(
        $queueMove->id,
        $session->id,
        $team->id,
        1,
        null,
    );

    expect($queueMove->fresh()->stp_team_id)->toBe($team->id);
    expect($queueMove->fresh()->status)->toBe(StpApproachStatus::Assigned);

    $queueRows = StpApproach::query()
        ->where('stp_session_id', $session->id)
        ->whereNull('stp_team_id')
        ->orderBy('position')
        ->get(['id', 'position'])
        ->toArray();

    expect($queueRows)->toBe([
        ['id' => $queueA->id, 'position' => 0],
        ['id' => $queueC->id, 'position' => 1],
    ]);

    $teamRows = StpApproach::query()
        ->where('stp_session_id', $session->id)
        ->where('stp_team_id', $team->id)
        ->orderBy('position')
        ->get(['id', 'position'])
        ->toArray();

    expect($teamRows)->toBe([
        ['id' => $teamA->id, 'position' => 0],
        ['id' => $queueMove->id, 'position' => 1],
        ['id' => $teamB->id, 'position' => 2],
    ]);
});
