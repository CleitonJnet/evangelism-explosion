<?php

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\TrainingNewChurch;
use App\Models\User;
use App\Services\Metrics\TrainingDiscipleshipMetricsService;
use App\Services\Metrics\TrainingFinanceMetricsService;
use App\Services\Metrics\TrainingOverviewMetricsService;
use App\Services\Metrics\TrainingRegistrationMetricsService;
use App\Services\Metrics\TrainingStpMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds grouped registration metrics for a training', function (): void {
    Storage::fake('public');

    $training = Training::factory()->create();
    $officialChurch = Church::factory()->create(['name' => 'Igreja Central']);
    $pendingChurch = ChurchTemp::query()->create([
        'name' => 'Congregacao em Plantacao',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
    ]);

    Storage::disk('public')->put('receipts/comprovante.png', 'fake-image');

    $pastor = User::factory()->create([
        'name' => 'Pastor Local',
        'church_id' => $officialChurch->id,
        'is_pastor' => 1,
    ]);
    $pendingStudent = User::factory()->create([
        'name' => 'Aluno Pendente',
        'church_id' => null,
        'church_temp_id' => $pendingChurch->id,
        'is_pastor' => 0,
    ]);

    $training->students()->attach($pastor->id, [
        'kit' => 1,
        'accredited' => 1,
        'payment' => 1,
        'payment_receipt' => 'receipts/comprovante.png',
    ]);
    $training->students()->attach($pendingStudent->id, [
        'kit' => 0,
        'accredited' => 0,
        'payment' => 0,
        'payment_receipt' => null,
    ]);

    $metrics = app(TrainingRegistrationMetricsService::class)->build($training);

    expect($metrics['totalRegistrations'])->toBe(2)
        ->and($metrics['totalChurches'])->toBe(2)
        ->and($metrics['totalPastors'])->toBe(1)
        ->and($metrics['totalAccredited'])->toBe(1)
        ->and($metrics['totalKits'])->toBe(1)
        ->and($metrics['totalPaymentReceipts'])->toBe(1)
        ->and($metrics['pendingChurchTempsCount'])->toBe(1)
        ->and($metrics['churchGroups'][0]['has_church_issue'])->toBeTrue()
        ->and($metrics['churchGroups'][0]['church_name'])->toContain('(PENDING)')
        ->and($metrics['churchGroups'][1]['registrations'][0]['payment_confirmed'])->toBeTrue();
});

it('builds overview and finance metrics from current training data', function (): void {
    $training = Training::factory()->create([
        'price' => '100,00',
        'price_church' => '20,00',
        'discount' => '10,00',
    ]);

    $churchA = Church::factory()->create();
    $churchB = Church::factory()->create();
    $actor = User::factory()->create();

    $studentOne = User::factory()->create(['church_id' => $churchA->id, 'is_pastor' => 1]);
    $studentTwo = User::factory()->create(['church_id' => $churchA->id, 'is_pastor' => 0]);
    $studentThree = User::factory()->create(['church_id' => $churchB->id, 'is_pastor' => 1]);

    $training->students()->attach($studentOne->id, ['kit' => 1, 'payment' => 1, 'accredited' => 1]);
    $training->students()->attach($studentTwo->id, ['kit' => 0, 'payment' => 0, 'accredited' => 0]);
    $training->students()->attach($studentThree->id, ['kit' => 1, 'payment' => 1, 'accredited' => 0]);

    TrainingNewChurch::query()->create([
        'training_id' => $training->id,
        'church_id' => Church::factory()->create()->id,
        'source_church_temp_id' => null,
        'created_by' => $actor->id,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'type' => StpApproachType::Visitor->value,
        'status' => StpApproachStatus::Done->value,
        'position' => 0,
        'person_name' => 'Pessoa 1',
        'gospel_explained_times' => 1,
        'people_count' => null,
        'result' => null,
        'payload' => [
            'listeners' => [
                ['name' => 'Pessoa 1', 'result' => 'decision'],
                ['name' => 'Pessoa 2', 'result' => 'decision'],
            ],
        ],
        'created_by_user_id' => $actor->id,
        'follow_up_scheduled_at' => now(),
    ]);

    $overview = app(TrainingOverviewMetricsService::class)->build($training);
    $finance = app(TrainingFinanceMetricsService::class)->build($training);

    expect($overview['totalRegistrations'])->toBe(3)
        ->and($overview['totalParticipatingChurches'])->toBe(2)
        ->and($overview['totalPastors'])->toBe(2)
        ->and($overview['totalUsedKits'])->toBe(2)
        ->and($overview['totalNewChurches'])->toBe(1)
        ->and($overview['totalDecisions'])->toBe(2)
        ->and($overview['resumoStp']['visita_agendada'])->toBe(1)
        ->and($overview['paidStudentsCount'])->toBe(2)
        ->and($overview['eeMinistryBalance'])->toBe('R$ 180,00')
        ->and($overview['hostChurchExpenseBalance'])->toBe('R$ 40,00')
        ->and($overview['totalReceivedFromRegistrations'])->toBe('R$ 220,00')
        ->and($finance)->toBe([
            'paidStudentsCount' => 2,
            'totalReceivedFromRegistrations' => 'R$ 220,00',
            'eeMinistryBalance' => 'R$ 180,00',
            'hostChurchExpenseBalance' => 'R$ 40,00',
        ]);
});

it('summarizes stp approaches for mentor and dashboard reuse', function (): void {
    $training = Training::factory()->create();
    $session = StpSession::factory()->create(['training_id' => $training->id]);
    $team = StpTeam::factory()->create(['stp_session_id' => $session->id]);
    $actor = User::factory()->create();

    StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => StpApproachType::Visitor->value,
        'status' => StpApproachStatus::Done->value,
        'position' => 0,
        'person_name' => 'Pessoa 1',
        'result' => StpApproachResult::Decision->value,
        'created_by_user_id' => $actor->id,
        'follow_up_scheduled_at' => now(),
    ]);

    StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => StpApproachType::Indication->value,
        'status' => StpApproachStatus::Reviewed->value,
        'position' => 1,
        'person_name' => 'Pessoa 2',
        'result' => StpApproachResult::NoDecisionInterested->value,
        'created_by_user_id' => $actor->id,
    ]);

    $summary = app(TrainingStpMetricsService::class)->summarizeApproaches(
        StpApproach::query()->where('training_id', $training->id)->get(),
    );

    expect($summary)->toBe([
        'total' => 2,
        'concluidas' => 1,
        'revisadas' => 1,
        'decisoes' => 1,
        'acompanhamentos' => 1,
    ]);
});

it('builds discipleship board metrics for the active stp session', function (): void {
    $course = Course::factory()->create([
        'execution' => 0,
        'min_stp_sessions' => 2,
    ]);
    $training = Training::factory()->create(['course_id' => $course->id]);
    $mentor = User::factory()->create(['name' => 'Mentor A']);
    $studentOne = User::factory()->create(['name' => 'Aluno 1']);
    $studentTwo = User::factory()->create(['name' => 'Aluno 2']);
    $actor = User::factory()->create();

    $training->mentors()->attach($mentor->id, ['created_by' => $actor->id]);
    $training->students()->attach([$studentOne->id, $studentTwo->id]);

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
        'label' => 'Saida de sabado',
    ]);

    $team = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    DB::table('stp_team_students')->insert([
        ['stp_team_id' => $team->id, 'user_id' => $studentOne->id, 'position' => 0],
        ['stp_team_id' => $team->id, 'user_id' => $studentTwo->id, 'position' => 1],
    ]);

    StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => StpApproachType::Visitor->value,
        'status' => StpApproachStatus::Done->value,
        'position' => 0,
        'person_name' => 'Pessoa 1',
        'gospel_explained_times' => 2,
        'people_count' => 3,
        'result' => StpApproachResult::Decision->value,
        'means_growth' => true,
        'follow_up_scheduled_at' => now(),
        'created_by_user_id' => $actor->id,
    ]);

    $metrics = app(TrainingDiscipleshipMetricsService::class)->buildTrainingBoard($training, $session->id);

    expect($metrics['mentorsCount'])->toBe(1)
        ->and($metrics['isLeadershipExecutionTraining'])->toBeTrue()
        ->and($metrics['activeSessionId'])->toBe($session->id)
        ->and($metrics['sessions'][0]['label'])->toContain('Saida de sabado')
        ->and($metrics['canCreateSession'])->toBeTrue()
        ->and($metrics['teams'][0]['mentor']['name'])->toBe('Mentor A')
        ->and($metrics['teams'][0]['students'])->toHaveCount(2)
        ->and($metrics['columnTotals'])->toBe([1, 0, 0, 0, 2, 3, 1, 0, 0, 0, 1, 1])
        ->and($metrics['canRandomizeTeams'])->toBeFalse()
        ->and($metrics['pendingStudents'])->toHaveCount(2);
});

it('summarizes the parallel discipleship track from stp payload data', function (): void {
    $training = Training::factory()->create();
    $session = StpSession::factory()->create(['training_id' => $training->id]);
    $actor = User::factory()->create();

    $inProgress = StpApproach::factory()
        ->for($training, 'training')
        ->for($session, 'session')
        ->withDiscipleship([
            'status' => 'in_progress',
            'started_at' => now()->subDays(3)->toDateTimeString(),
            'completed_at' => null,
            'sessions_planned' => 2,
            'sessions_completed' => 1,
            'next_step' => 'Nova visita',
            'next_step_registered_at' => now()->subDay()->toDateTimeString(),
            'local_church_referral_at' => null,
            'follow_up_pending' => true,
        ])
        ->create([
            'created_by_user_id' => $actor->id,
        ]);

    $completed = StpApproach::factory()
        ->for($training, 'training')
        ->for($session, 'session')
        ->withDiscipleship([
            'status' => 'completed',
            'started_at' => now()->subDays(8)->toDateTimeString(),
            'completed_at' => now()->subDay()->toDateTimeString(),
            'sessions_planned' => 3,
            'sessions_completed' => 3,
            'next_step' => 'Integração',
            'next_step_registered_at' => now()->subDays(2)->toDateTimeString(),
            'local_church_referral_at' => now()->subDay()->toDateTimeString(),
            'follow_up_pending' => false,
        ])
        ->create([
            'created_by_user_id' => $actor->id,
        ]);

    $summary = app(TrainingDiscipleshipMetricsService::class)->summarizeParallelTrack([$inProgress, $completed]);

    expect($summary)->toBe([
        'people_in_follow_up' => 1,
        'started' => 2,
        'completed' => 1,
        'local_church_referrals' => 1,
        'pending_follow_ups' => 1,
        'next_steps_registered' => 2,
        'sessions_planned' => 5,
        'sessions_completed' => 4,
    ]);
});

it('blocks new stp sessions when the last session has no students distributed', function (): void {
    $training = Training::factory()->create();
    $mentor = User::factory()->create();
    $student = User::factory()->create();
    $actor = User::factory()->create();

    $training->mentors()->attach($mentor->id, ['created_by' => $actor->id]);
    $training->students()->attach($student->id);

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    $state = app(TrainingDiscipleshipMetricsService::class)->buildCreateSessionState($training);

    expect($state['canCreateSession'])->toBeFalse()
        ->and($state['createSessionBlockedReason'])->toContain('sessão anterior');
});
