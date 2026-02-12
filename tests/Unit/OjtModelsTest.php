<?php

declare(strict_types=1);

use App\Models\OjtReport;
use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\OjtTeamTrainee;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('connects ojt models through their relationships', function () {
    $training = Training::factory()->create();
    $mentor = User::factory()->create();
    $trainee = User::factory()->create();

    $session = OjtSession::create([
        'training_id' => $training->id,
        'date' => now()->toDateString(),
        'starts_at' => '09:00:00',
        'ends_at' => '11:00:00',
        'week_number' => 1,
        'status' => 'planned',
        'meta' => ['location' => 'Neighborhood'],
    ]);

    $team = OjtTeam::create([
        'ojt_session_id' => $session->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    OjtTeamTrainee::create([
        'ojt_team_id' => $team->id,
        'trainee_id' => $trainee->id,
        'order' => 1,
    ]);

    $report = OjtReport::create([
        'ojt_team_id' => $team->id,
        'contact_type' => 'home',
        'gospel_presentations' => 1,
        'listeners_count' => 2,
        'results_decisions' => 0,
        'results_interested' => 1,
        'results_rejection' => 1,
        'results_assurance' => 0,
        'follow_up_scheduled' => true,
        'outline_participation' => ['Grace' => ['mentor' => 'testimony']],
        'lesson_learned' => 'Follow-up matters.',
        'public_report' => ['summary' => 'Good visit'],
        'submitted_at' => now(),
    ]);

    expect($session->training->is($training))->toBeTrue()
        ->and($team->session->is($session))->toBeTrue()
        ->and($team->mentor->is($mentor))->toBeTrue()
        ->and($team->trainees->first()->trainee->is($trainee))->toBeTrue()
        ->and($report->team->is($team))->toBeTrue();
});
