<?php

declare(strict_types=1);

use App\Livewire\Pages\App\Student\Training\Show;
use App\Models\OjtReport;
use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\OjtTeamTrainee;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows upcoming ojt assignments with report summary', function () {
    $student = User::factory()->create();
    $mentor = User::factory()->create();
    $teammate = User::factory()->create();

    $training = Training::factory()->create();
    $training->students()->attach($student->id, [
        'payment' => false,
        'payment_receipt' => null,
    ]);

    $session = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->addDay()->toDateString(),
        'week_number' => 1,
        'status' => 'planned',
    ]);

    $team = OjtTeam::query()->create([
        'ojt_session_id' => $session->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    OjtTeamTrainee::query()->create([
        'ojt_team_id' => $team->id,
        'trainee_id' => $student->id,
        'order' => 1,
    ]);

    OjtTeamTrainee::query()->create([
        'ojt_team_id' => $team->id,
        'trainee_id' => $teammate->id,
        'order' => 2,
    ]);

    OjtReport::query()->create([
        'ojt_team_id' => $team->id,
        'gospel_presentations' => 2,
        'listeners_count' => 3,
        'results_decisions' => 1,
        'results_interested' => 1,
        'results_rejection' => 0,
        'results_assurance' => 0,
        'follow_up_scheduled' => true,
        'lesson_learned' => 'Trust grows through consistent mentoring.',
        'submitted_at' => now(),
    ]);

    $this->actingAs($student);

    Livewire::test(Show::class, ['training' => $training])
        ->assertSee('OJT Sessions')
        ->assertSee($mentor->name)
        ->assertSee($teammate->name)
        ->assertSee('Report Summary');
});
