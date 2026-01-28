<?php

declare(strict_types=1);

use App\Models\OjtReport;
use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('computes ojt report summary totals for a training', function () {
    $training = Training::factory()->create(['ojt_count_override' => 3]);

    $mentor = User::factory()->create();

    $firstSession = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->subDay()->toDateString(),
        'week_number' => 1,
        'status' => 'planned',
    ]);

    $firstTeam = OjtTeam::query()->create([
        'ojt_session_id' => $firstSession->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    OjtReport::query()->create([
        'ojt_team_id' => $firstTeam->id,
        'gospel_presentations' => 2,
        'listeners_count' => 3,
        'results_decisions' => 1,
        'results_interested' => 1,
        'results_rejection' => 0,
        'results_assurance' => 0,
        'follow_up_scheduled' => true,
        'submitted_at' => now(),
    ]);

    $secondSession = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->toDateString(),
        'week_number' => 2,
        'status' => 'planned',
    ]);

    $secondTeam = OjtTeam::query()->create([
        'ojt_session_id' => $secondSession->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    OjtReport::query()->create([
        'ojt_team_id' => $secondTeam->id,
        'gospel_presentations' => 1,
        'listeners_count' => 2,
        'results_decisions' => 0,
        'results_interested' => 1,
        'results_rejection' => 1,
        'results_assurance' => 1,
        'follow_up_scheduled' => false,
        'submitted_at' => now(),
    ]);

    OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->addDay()->toDateString(),
        'week_number' => 3,
        'status' => 'planned',
    ]);

    $summary = $training->ojtReportSummary();

    expect($summary['completed_sessions'])->toBe(2)
        ->and($summary['expected_sessions'])->toBe(3)
        ->and($summary['gospel_presentations'])->toBe(3)
        ->and($summary['listeners_count'])->toBe(5)
        ->and($summary['results_decisions'])->toBe(1)
        ->and($summary['results_interested'])->toBe(2)
        ->and($summary['results_rejection'])->toBe(1)
        ->and($summary['results_assurance'])->toBe(1)
        ->and($summary['follow_up_scheduled'])->toBe(1);
});
