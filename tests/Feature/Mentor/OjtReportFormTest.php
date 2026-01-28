<?php

declare(strict_types=1);

use App\Livewire\Pages\App\Mentor\Ojt\ReportForm;
use App\Models\OjtReport;
use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\OjtTeamTrainee;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createMentorForReport(): User
{
    $role = Role::query()->firstOrCreate(['name' => 'Mentor']);
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function seedReportTeam(User $mentor): OjtTeam
{
    $training = Training::factory()->create();
    $session = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->toDateString(),
        'week_number' => 1,
        'status' => 'planned',
    ]);

    $team = OjtTeam::query()->create([
        'ojt_session_id' => $session->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    $trainees = User::factory()->count(2)->create();

    foreach ($trainees as $index => $trainee) {
        OjtTeamTrainee::query()->create([
            'ojt_team_id' => $team->id,
            'trainee_id' => $trainee->id,
            'order' => $index + 1,
        ]);
    }

    return $team->fresh(['trainees']);
}

it('saves ojt report as draft', function () {
    $mentor = createMentorForReport();
    $team = seedReportTeam($mentor);

    $this->actingAs($mentor);

    Livewire::test(ReportForm::class, ['team' => $team])
        ->set('gospel_presentations', 2)
        ->set('listeners_count', 4)
        ->set('results_decisions', 1)
        ->set('results_interested', 1)
        ->set('results_rejection', 1)
        ->set('results_assurance', 0)
        ->set('follow_up_scheduled', true)
        ->set('lesson_learned', 'Follow-up strengthens discipleship.')
        ->set('contactTypeCounts', [['type' => 'Home', 'count' => 2]])
        ->set("outline.{$team->trainees->first()->trainee_id}.grace.enabled", true)
        ->set("outline.{$team->trainees->first()->trainee_id}.grace.type", 'testimony')
        ->set("outline.{$team->trainees->first()->trainee_id}.grace.description", 'Shared personal story.')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $report = OjtReport::query()->where('ojt_team_id', $team->id)->first();

    expect($report)->not->toBeNull()
        ->and($report?->submitted_at)->toBeNull()
        ->and($report?->is_locked)->toBeFalse()
        ->and($report?->contact_type_counts)->toBe([['type' => 'Home', 'count' => 2]])
        ->and($report?->lesson_learned)->toBe('Follow-up strengthens discipleship.');
});

it('locks report after submit', function () {
    $mentor = createMentorForReport();
    $team = seedReportTeam($mentor);

    $this->actingAs($mentor);

    Livewire::test(ReportForm::class, ['team' => $team])
        ->set('gospel_presentations', 1)
        ->set('listeners_count', 2)
        ->set('results_decisions', 0)
        ->set('results_interested', 1)
        ->set('results_rejection', 1)
        ->set('results_assurance', 0)
        ->set('contactTypeCounts', [['type' => 'Street', 'count' => 1]])
        ->call('submitReport')
        ->assertHasNoErrors();

    $report = OjtReport::query()->where('ojt_team_id', $team->id)->first();

    expect($report)->not->toBeNull()
        ->and($report?->submitted_at)->not->toBeNull()
        ->and($report?->is_locked)->toBeTrue();

    $this->actingAs($mentor);

    Livewire::test(ReportForm::class, ['team' => $team])
        ->call('saveDraft')
        ->assertForbidden();
});
