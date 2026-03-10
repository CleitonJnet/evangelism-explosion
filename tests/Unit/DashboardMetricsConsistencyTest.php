<?php

use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use App\Services\Dashboard\DirectorDashboardService;
use App\Services\Dashboard\TeacherDashboardService;
use App\Services\Metrics\TrainingOverviewMetricsService;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function assignDashboardRole(User $user, string $roleName): User
{
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function forceTrainingIntoCurrentYear(Training $training): void
{
    foreach ($training->eventDates->values() as $index => $eventDate) {
        $eventDate->update([
            'date' => now()->subDays(30)->addDays($index)->toDateString(),
        ]);
    }
}

it('keeps teacher dashboard metrics aligned with the training overview service for a single visible training', function (): void {
    $teacher = assignDashboardRole(User::factory()->create(), 'Teacher');
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'price' => '100,00',
        'price_church' => '20,00',
        'discount' => '10,00',
    ]);
    forceTrainingIntoCurrentYear($training);

    $student = User::factory()->create(['is_pastor' => true]);
    $training->students()->attach($student->id, [
        'payment' => 1,
        'kit' => 1,
        'accredited' => 1,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    StpApproach::factory()
        ->for($training, 'training')
        ->for($session, 'session')
        ->withDiscipleship([
            'status' => 'in_progress',
            'sessions_planned' => 2,
            'sessions_completed' => 1,
            'follow_up_pending' => true,
        ])
        ->create([
            'created_by_user_id' => $teacher->id,
            'status' => 'done',
            'result' => 'decision',
            'gospel_explained_times' => 1,
            'people_count' => 1,
            'follow_up_scheduled_at' => now(),
        ]);

    $overview = app(TrainingOverviewMetricsService::class)->build($training->fresh());
    $dashboard = app(TeacherDashboardService::class)->build($teacher->fresh(), DashboardPeriod::Year);

    expect(collect($dashboard['kpis'])->firstWhere('key', 'registrations')['value'])->toBe($overview['totalRegistrations'])
        ->and(collect($dashboard['kpis'])->firstWhere('key', 'paid_students')['value'])->toBe($overview['paidStudentsCount'])
        ->and(collect($dashboard['evangelisticImpact'])->firstWhere('key', 'decisions')['value'])->toBe($overview['totalDecisions'])
        ->and(collect($dashboard['evangelisticImpact'])->firstWhere('key', 'scheduled_visits')['value'])->toBe($overview['resumoStp']['visita_agendada']);
});

it('keeps director dashboard metrics aligned with the training overview service for a single training', function (): void {
    $director = assignDashboardRole(User::factory()->create(), 'Director');
    $teacher = assignDashboardRole(User::factory()->create(), 'Teacher');
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'price' => '80,00',
        'price_church' => '10,00',
        'discount' => '5,00',
    ]);
    forceTrainingIntoCurrentYear($training);

    $student = User::factory()->create();
    $training->students()->attach($student->id, [
        'payment' => 1,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    StpApproach::factory()
        ->for($training, 'training')
        ->for($session, 'session')
        ->withDiscipleship([
            'status' => 'in_progress',
            'sessions_planned' => 1,
            'sessions_completed' => 0,
            'follow_up_pending' => true,
        ])
        ->create([
            'created_by_user_id' => $teacher->id,
            'status' => 'done',
            'result' => 'decision',
            'gospel_explained_times' => 2,
            'people_count' => 2,
            'follow_up_scheduled_at' => now(),
        ]);

    $overview = app(TrainingOverviewMetricsService::class)->build($training->fresh());
    $dashboard = app(DirectorDashboardService::class)->build(DashboardPeriod::Year);

    expect(collect($dashboard['kpis'])->firstWhere('key', 'registrations')['value'])->toBe($overview['totalRegistrations'])
        ->and(collect($dashboard['kpis'])->firstWhere('key', 'paid_students')['value'])->toBe($overview['paidStudentsCount'])
        ->and(collect($dashboard['kpis'])->firstWhere('key', 'decisions')['value'])->toBe($overview['totalDecisions'])
        ->and(collect($dashboard['kpis'])->firstWhere('key', 'scheduled_visits')['value'])->toBe($overview['resumoStp']['visita_agendada'])
        ->and(collect($dashboard['kpis'])->firstWhere('key', 'ee_balance')['value'])->toBe($overview['eeMinistryBalance']);
});
